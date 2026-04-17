<?php

namespace App\Http\Controllers;
use App\Models\Folder;
use App\Models\Asset;
use App\Models\AssetVersion;
use App\Models\Project;
use App\Models\User;
use App\Models\WorkspaceUser;
use App\Models\ProjectCollaborator;
use App\Notifications\AssetUploaded;
use App\Notifications\AssetDeleted;
use App\Notifications\FolderCreated;
use App\Notifications\FolderDeleted;
use App\Notifications\NewVersionUploaded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class FolderController extends Controller
{
    private array $allowedMimes = [
        'image/jpeg',
        'image/png', 
        'image/gif',
        'image/webp',
        'video/mp4',
        'video/mov',
        'video/webm',
        'application/pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/plain',
        'text/markdown',
    ];

    private array $maxSizes = [
        'image' => 50 * 1024 * 1024, // 50MB
        'video' => 500 * 1024 * 1024, // 500MB
        'document' => 50 * 1024 * 1024, // 50MB
        'text' => 50 * 1024 * 1024, // 50MB
    ];

    public function index(Request $request)
    {
        $folder = $request->input('folder', 'root');
        $folderPath = $folder === 'root' ? 'uploads' : 'uploads/' . trim($folder, '/');
        
        $files = [];
        $folders = [];
        
        // Get files from database for this folder
        $folderId = null;
        if ($folder !== 'root') {
            $folderModel = Folder::where('name', $folder)->first();
            $folderId = $folderModel ? $folderModel->id : null;
        }
        
        if ($folderId || $folder === 'root') {
            $assets = Asset::where('folder_id', $folderId)
                ->where('uploaded_by', auth()->id())
                ->with('folder')
                ->get();
            
            $files = $assets->map(function ($asset) {
                return [
                    'id' => $asset->id,
                    'name' => $asset->original_name ?? $asset->name,
                    'file_path' => $asset->file_path,
                    'file_type' => $asset->file_type,
                    'file_size' => $asset->file_size,
                    'path' => $asset->file_path,
                    'url' => Storage::url($asset->file_path),
                    'is_image' => $asset->isImage(),
                    'is_video' => $asset->isVideo(),
                    'is_document' => $asset->isDocument(),
                    'is_text' => $asset->isText(),
                    'formatted_size' => $asset->formatted_size,
                    'created_at' => $asset->created_at->format('Y-m-d H:i:s'),
                ];
            });
        }
        
        // Get folders from file system
        if (Storage::disk('public')->exists($folderPath)) {
            $folderItems = Storage::disk('public')->directories($folderPath);
            foreach ($folderItems as $dir) {
                $folders[] = [
                    'name' => basename($dir),
                    'path' => $dir,
                    'parent_folder' => $folder,
                ];
            }
        }

        return view('file-manager', [
            'files' => $files,
            'folders' => $folders,
            'currentFolder' => $folder,
            'breadcrumb' => $this->generateBreadcrumb($folder)
        ]);
    }

    public function store(Request $request)
    {
        // Check write permissions - must be project owner or collaborator
        $projectId = $request->input('project_id');
        $project = Project::find($projectId);

        \Log::info('Folder creation attempt', [
            'project_id' => $projectId,
            'parent_folder_id' => $request->input('parent_folder_id'),
            'name' => $request->input('name'),
            'user_id' => auth()->id()
        ]);

        if (!$project || !$this->hasWriteAccess($project, $request->user())) {
            return redirect()->back()
                ->withErrors(['permission' => 'You do not have permission to create folders in this project'])
                ->withInput();
        }

        // Manual duplicate check for better error handling
        $name = $request->input('name');
        $projectId = $request->input('project_id');
        $parentFolderId = $request->input('parent_folder_id');

        \Log::info('Checking for duplicate folder', [
            'name' => $name,
            'project_id' => $projectId,
            'parent_folder_id' => $parentFolderId,
            'all_folders_in_project' => Folder::where('project_id', $projectId)->get()->pluck('name', 'id')->toArray(),
        ]);

        // Check for duplicate in the same parent folder
        $query = Folder::where('name', $name)
            ->where('project_id', $projectId);

        if ($parentFolderId) {
            $query->where('parent_folder_id', $parentFolderId);
        } else {
            $query->whereNull('parent_folder_id');
        }

        $existingFolder = $query->first();

        \Log::info('Duplicate folder check result', [
            'found' => $existingFolder ? true : false,
            'existing_folder_id' => $existingFolder ? $existingFolder->id : null,
            'existing_folder_name' => $existingFolder ? $existingFolder->name : null,
        ]);

        if ($existingFolder) {
            \Log::error('Duplicate folder detected', [
                'name' => $name,
                'project_id' => $projectId,
                'parent_folder_id' => $parentFolderId,
                'existing_folder_id' => $existingFolder->id
            ]);
            return redirect()->back()
                ->with('error', "Folder '{$name}' already exists in this location")
                ->withInput();
        }

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'parent_folder_id' => 'nullable|exists:folders,id',
            'project_id' => 'required|exists:projects,id',
        ]);

        if ($validator->fails()) {
            \Log::error('Folder validation failed', [
                'errors' => $validator->errors()->toArray(),
                'project_id' => $request->input('project_id'),
                'parent_folder_id' => $request->input('parent_folder_id'),
                'name' => $request->input('name')
            ]);
            return redirect()->back()
                ->with('error', $validator->errors()->first())
                ->withInput();
        }

        try {
            // Get the maximum order value for this parent_id
            $parentId = $request->input('parent_folder_id');
            $maxOrder = Folder::getMaxOrder($parentId);
            $newOrder = $maxOrder + 1;

            $folder = Folder::create([
                'name' => $request->input('name'),
                'project_id' => $request->input('project_id'),
                'parent_folder_id' => $parentId,
                'order' => $newOrder,
            ]);

            // Load project relationship for notification
            $folder->load('project');

            // Notify project members about the new folder
            if ($folder->project) {
                $this->notifyProjectMembers(
                    $folder->project,
                    new FolderCreated($folder, auth()->user()),
                    [auth()->id()]
                );
            }

            // Handle different redirect scenarios
            if ($folder->parent_folder_id) {
                // Redirect to parent folder
                return redirect()->route('folders.show', $folder->parent_folder_id)
                    ->with('success', 'Folder created successfully');
            } else {
                // Redirect to project
                return redirect()->route('projects.show', $folder->project_id)
                    ->with('success', 'Folder created successfully');
            }
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['create' => 'Failed to create folder: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function show(Folder $folder)
    {
        // Load folder with children, parent, and project
        $folder->load(['children', 'parent', 'project']);

        // Filter assets based on user role
        $user = auth()->user();
        $assetsQuery = $folder->assets();

        // Check if user is a reviewer/admin for this project
        $isReviewer = $user->canApproveInProject($folder->project);
        $isUploader = $user->canUploadToProject($folder->project);

        if ($isReviewer && !$isUploader) {
            // User is only a reviewer - don't show draft assets
            $assetsQuery->where('status', '!=', 'draft');
        } elseif ($isReviewer && $isUploader) {
            // User is both reviewer and uploader - show in_review assets + their own draft assets
            $assetsQuery->where(function($query) use ($user) {
                $query->where('status', '!=', 'draft')
                      ->orWhere('uploaded_by', $user->id);
            });
        } elseif (!$isReviewer && $isUploader) {
            // User is only an uploader - show only their own assets
            $assetsQuery->where('uploaded_by', $user->id);
        }

        $folder->setRelation('assets', $assetsQuery->get());

        // Get breadcrumb path
        $breadcrumbs = $this->getBreadcrumbs($folder);

        // Check if user has read-only access (workspace admin without write permissions)
        $readOnly = !$this->hasWriteAccess($folder->project, auth()->user());

        return view('folder.show', compact('folder', 'breadcrumbs', 'readOnly'));
    }

    public function update(Request $request, Folder $folder)
    {
        // Check write permissions
        $project = $folder->project;
        
        if (!$project || !$this->hasWriteAccess($project, $request->user())) {
            return redirect()->back()
                ->withErrors(['permission' => 'You do not have permission to rename folders in this project'])
                ->withInput();
        }

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('folders')
                    ->where('project_id', $folder->project_id)
                    ->where('parent_folder_id', $folder->parent_folder_id)
                    ->ignore($folder->id),
            ],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $folder->update([
                'name' => $request->input('name'),
            ]);

            // Stay on the current page (parent folder or project)
            return redirect()->back()
                ->with('success', 'Folder renamed successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['update' => 'Failed to rename folder: ' . $e->getMessage()])
                ->withInput();
        }
    }

    private function getBreadcrumbs(Folder $folder)
    {
        $breadcrumbs = [];
        $current = $folder;
        
        while ($current) {
            array_unshift($breadcrumbs, $current);
            $current = $current->parent;
        }
        
        return $breadcrumbs;
    }

    public function destroy(Folder $folder)
    {
        // Check write permissions - must be project owner or collaborator
        $project = $folder->project;

        if (!$project || !$this->hasWriteAccess($project, auth()->user())) {
            return redirect()->back()
                ->withErrors(['permission' => 'You do not have permission to delete folders in this project']);
        }

        // Check if folder has content (files or subfolders)
        $hasFiles = $folder->assets()->count() > 0;
        $hasSubfolders = $folder->children()->count() > 0;

        if ($hasFiles || $hasSubfolders) {
            $message = [];
            if ($hasFiles) {
                $message[] = 'files';
            }
            if ($hasSubfolders) {
                $message[] = 'subfolders';
            }

            return redirect()->back()
                ->with('error', 'Cannot delete folder: It contains ' . implode(' and ', $message) . '. Please delete the content first.');
        }

        // Store parent information before deletion
        $parentFolder = $folder->parent;
        $project = $folder->project;
        $parentId = $folder->parent_folder_id;

        // Store folder data for notification
        $folderName = $folder->name;
        $projectName = $project ? $project->name : null;
        $parentFolderName = $parentFolder ? $parentFolder->name : null;

        // Notify project members about the folder deletion
        if ($project) {
            $this->notifyProjectMembers(
                $project,
                new FolderDeleted($folderName, $projectName, $parentFolderName, auth()->user()),
                [auth()->id()]
            );
        }

        // Delete folder and reorder siblings in transaction
        \DB::transaction(function () use ($folder, $parentId) {
            $this->deleteFolderRecursive($folder);
            // Reorder remaining siblings to maintain sequential order (0,1,2,...)
            Folder::reorderSiblings($parentId);
        });

        // Determine redirect destination
        if ($parentFolder) {
            // If folder has a parent, redirect to parent folder
            return redirect()->route('folders.show', $parentFolder->id)
                ->with('success', 'Folder deleted successfully');
        } else {
            // If folder is in project root, redirect to project
            return redirect()->route('projects.show', $project->id)
                ->with('success', 'Folder deleted successfully');
        }
    }

    private function deleteFolderRecursive(Folder $folder)
    {
        foreach ($folder->children as $child) {
            $this->deleteFolderRecursive($child);
        }
        $folder->delete();
    }

    public function uploadFiles(Request $request)
    {
        // Check write permissions
        $folderId = $request->input('folder');
        $project = null;
        
        if (is_numeric($folderId)) {
            $folder = Folder::find($folderId);
            if ($folder) {
                $project = $folder->project;
            }
        }
        
        if (!$project) {
            return redirect()->back()
                ->withErrors(['permission' => 'You do not have permission to upload files to this project'])
                ->withInput();
        }
        
        if (!$this->hasWriteAccess($project, $request->user())) {
            return redirect()->back()
                ->withErrors(['permission' => 'You do not have permission to upload files to this project'])
                ->withInput();
        }

        // Debug: Log incoming request
        \Log::info('Upload request received', [
            'files' => $request->hasFile('files') ? 'Yes' : 'No',
            'folder' => $request->input('folder'),
            'all_data' => $request->all()
        ]);

        $validator = Validator::make($request->all(), [
            'files' => 'required|array|max:10',
            'files.*' => 'required|file|max:512000', // 500MB max per file
            'folder' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            \Log::error('Validation failed', $validator->errors()->toArray());
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $uploadedFiles = [];
        $errors = [];
        $folder = $request->input('folder', 'root');
        $folderId = $request->input('folder');

        // Handle different redirect scenarios
        $redirectRoute = 'folder-manager';
        $redirectParams = ['folder' => $folder];
        
        // If folder is a numeric ID, we're coming from folder show page
        if (is_numeric($folderId)) {
            $folderModel = Folder::find($folderId);
            if ($folderModel) {
                $folder = $folderModel->name;
                $redirectRoute = 'folders.show';
                $redirectParams = ['folder' => $folderId];
            }
        }

        \Log::info('Processing files', ['count' => count($request->file('files'))]);

        foreach ($request->file('files') as $index => $file) {
            try {
                \Log::info('Processing file', ['index' => $index, 'filename' => $file->getClientOriginalName()]);
                $result = $this->processFile($file, $folder, $folderId);
                if ($result['success']) {
                    $uploadedFiles[] = $result['file'];
                    \Log::info('File processed successfully', ['filename' => $file->getClientOriginalName()]);
                } else {
                    $errors["files.{$index}"] = $result['error'];
                    \Log::error('File processing failed', ['filename' => $file->getClientOriginalName(), 'error' => $result['error']]);
                }
            } catch (\Exception $e) {
                $errors["files.{$index}"] = 'Upload failed: ' . $e->getMessage();
                \Log::error('File upload exception', ['filename' => $file->getClientOriginalName(), 'exception' => $e->getMessage()]);
            }
        }

        \Log::info('Upload complete', [
            'uploaded_count' => count($uploadedFiles),
            'error_count' => count($errors),
            'redirect_route' => $redirectRoute,
            'redirect_params' => $redirectParams
        ]);

        if (count($uploadedFiles) > 0) {
            return redirect()->route($redirectRoute, $redirectParams)
                ->with('success', "Successfully uploaded " . count($uploadedFiles) . " files");
        } else {
            \Log::error('No files uploaded successfully', ['errors' => $errors]);
            return redirect()->back()
                ->withErrors(['upload' => 'No files were uploaded successfully'])
                ->withInput();
        }
    }

    public function deleteFile(Request $request)
    {
        // Check write permissions
        $path = $request->input('path');
        $asset = Asset::where('file_path', $path)->first();
        
        if ($asset && $asset->project_id) {
            $project = Project::find($asset->project_id);
            if ($project && !$this->hasWriteAccess($project, auth()->user())) {
                return redirect()->back()
                    ->withErrors(['permission' => 'You do not have permission to delete files in this project']);
            }
        }

        $validator = Validator::make($request->all(), [
            'path' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $path = $request->input('path');

        try {
            if (!Storage::disk('public')->exists($path)) {
                return redirect()->back()
                    ->withErrors(['path' => 'File not found']);
            }

            // Get asset data before deletion for notification
            $asset = Asset::where('file_path', $path)->with(['folder', 'project'])->first();
            
            if ($asset) {
                // Store asset data for notification
                $assetName = $asset->name;
                $assetType = $asset->file_type;
                $projectName = $asset->project ? $asset->project->name : null;
                $folderName = $asset->folder ? $asset->folder->name : null;
                $project = $asset->project;

                // Notify project members about the asset deletion
                if ($project) {
                    $this->notifyProjectMembers(
                        $project,
                        new AssetDeleted($assetName, $assetType, $projectName, $folderName, auth()->user()),
                        [auth()->id()]
                    );
                }

                // Delete from database
                $asset->delete();
            }

            // Delete from file system
            Storage::disk('public')->delete($path);

            return redirect()->back()
                ->with('success', 'File deleted successfully');
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['delete' => 'Failed to delete file']);
        }
    }

    /**
     * Check if user has write access to project (owner or admin only).
     * Reviewers and viewers do NOT have write access.
     */
    private function hasWriteAccess(Project $project, $user): bool
    {
        return $project->canUserUpload($user);
    }

    private function processFile($file, string $folder, $folderId = null): array
    {
        try {
            \Log::info('processFile started', [
                'folder' => $folder,
                'folderId' => $folderId,
                'filename' => $file->getClientOriginalName()
            ]);

            $mimeType = $file->getMimeType();
            $size = $file->getSize();

            // Validate mime type
            if (!in_array($mimeType, $this->allowedMimes)) {
                return ['success' => false, 'error' => "File type '{$mimeType}' not allowed"];
            }

            // Validate size
            $fileType = $this->getFileType($mimeType);
            $maxSize = $this->maxSizes[$fileType] ?? 50 * 1024 * 1024;

            if ($size > $maxSize) {
                $maxSizeMB = $maxSize / (1024 * 1024);
                return ['success' => false, 'error' => "File size exceeds {$maxSizeMB}MB limit"];
            }

            // Generate unique filename
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $hash = hash_file('sha256', $file->getPathname());

            // Create folder path - if folderId is provided, use it to get the correct folder
            if ($folderId) {
                $folderModel = Folder::find($folderId);
                if ($folderModel) {
                    // Build the full path from the folder hierarchy
                    $folderPath = $this->getFolderPath($folderModel);
                } else {
                    $folderPath = $folder === 'root' ? 'uploads' : 'uploads/' . trim($folder, '/');
                }
            } else {
                $folderPath = $folder === 'root' ? 'uploads' : 'uploads/' . trim($folder, '/');
            }

            \Log::info('Folder path determined', [
                'folderPath' => $folderPath,
                'folderId' => $folderId,
            ]);

            // Ensure folder exists
            if (!Storage::disk('public')->exists($folderPath)) {
                Storage::disk('public')->makeDirectory($folderPath);
                \Log::info('Created folder path', ['folderPath' => $folderPath]);
            }

            // Check for duplicates in the same folder
            $existingFiles = Storage::disk('public')->files($folderPath);
            foreach ($existingFiles as $existingFile) {
                $existingPath = storage_path('app/public/' . $existingFile);
                if (file_exists($existingPath)) {
                    $existingHash = hash_file('sha256', $existingPath);
                    if ($existingHash === $hash) {
                        return ['success' => false, 'error' => 'File already exists in this folder'];
                    }
                }
            }

            // Store file
            $path = $file->storeAs($folderPath, $filename, 'public');

            if (!$path) {
                return ['success' => false, 'error' => 'Failed to store file'];
            }

            // Use the provided folderId
            if ($folderId === null && $folder !== 'root') {
                $folderModel = Folder::where('name', $folder)->first();
                $folderId = $folderModel ? $folderModel->id : null;
            }

            // Save to database
            $asset = Asset::create([
                'name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $this->getFileType($mimeType),
                'file_size' => $size,
                'folder_id' => $folderId,
                'project_id' => $this->getProjectIdFromFolder($folderId),
                'uploaded_by' => auth()->id(),
                'version' => 1.0,
            ]);

            \Log::info('Asset created in FolderController', [
                'asset_id' => $asset->id,
                'asset_name' => $asset->name,
                'uploaded_by' => $asset->uploaded_by,
                'version' => $asset->version,
                'current_version_id' => $asset->current_version_id,
            ]);

            try {
                // Create initial version record
                $assetVersion = \App\Models\AssetVersion::create([
                    'asset_id' => $asset->id,
                    'version_number' => 1.0,
                    'name' => $asset->name,
                    'file_path' => $asset->file_path,
                    'file_type' => $asset->file_type,
                    'file_size' => $asset->file_size,
                    'hash' => $hash,
                    'status' => 'draft',
                    'uploaded_by' => auth()->id(),
                ]);

                \Log::info('AssetVersion created in FolderController', [
                    'asset_version_id' => $assetVersion->id,
                    'asset_id' => $assetVersion->asset_id,
                    'version_number' => $assetVersion->version_number,
                    'uploaded_by' => $assetVersion->uploaded_by,
                    'hash' => $assetVersion->hash,
                ]);

                // Update asset with current version ID
                $asset->update(['current_version_id' => $assetVersion->id]);

                \Log::info('Asset updated with current_version_id in FolderController', [
                    'asset_id' => $asset->id,
                    'current_version_id' => $asset->current_version_id,
                ]);

                // Notify project members about the new asset
                if ($asset->project_id) {
                    $project = Project::find($asset->project_id);
                    if ($project) {
                        $this->notifyProjectMembers(
                            $project,
                            new AssetUploaded($asset, auth()->user()),
                            auth()->id()
                        );
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Failed to create AssetVersion in FolderController', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'asset_id' => $asset->id,
                ]);
                throw $e;
            }

            return [
                'success' => true,
                'file' => [
                    'id' => $asset->id,
                    'name' => $asset->name,
                    'file_path' => $asset->file_path,
                    'file_type' => $asset->file_type,
                    'file_size' => $asset->file_size,
                    'folder_id' => $asset->folder_id,
                    'url' => Storage::url($asset->file_path),
                    'is_image' => $asset->file_type === 'image',
                    'is_video' => $asset->file_type === 'video',
                    'is_document' => in_array($asset->file_type, ['pdf', 'doc']),
                    'is_text' => $asset->file_type === 'text',
                    'formatted_size' => $asset->formatted_size,
                    'created_at' => $asset->created_at->format('Y-m-d H:i:s'),
                ],
            ];
            
        } catch (\Exception $e) {
            Log::error('Error processing file', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()->toISOString()
            ]);
            
            return ['success' => false, 'error' => 'Upload failed: ' . $e->getMessage()];
        }
    }

    private function getFileType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) return 'image';
        if (str_starts_with($mimeType, 'video/')) return 'video';
        if (str_starts_with($mimeType, 'text/')) return 'text';
        if ($mimeType === 'text/markdown') return 'text';
        if ($mimeType === 'application/pdf') return 'pdf';
        if (in_array($mimeType, [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])) return 'doc';
        
        return 'doc'; // default fallback
    }

    private function getProjectIdFromFolder(?int $folderId): ?int
    {
        if (!$folderId) {
            return null;
        }
        
        $folder = Folder::find($folderId);
        return $folder ? $folder->project_id : null;
    }

    private function generateBreadcrumb(string $folder): array
    {
        if ($folder === 'root') {
            return [];
        }

        $parts = explode('/', str_replace('uploads/', '', $folder));
        $breadcrumb = [];
        $currentPath = '';

        foreach ($parts as $part) {
            $currentPath .= ($currentPath ? '/' : '') . $part;
            $breadcrumb[] = [
                'name' => $part,
                'path' => 'uploads/' . $currentPath
            ];
        }

        return $breadcrumb;
    }

    /**
     * Build the full folder path from folder hierarchy
     */
    private function getFolderPath(Folder $folder): string
    {
        $pathParts = [];
        $current = $folder;

        // Build path from folder up to root
        while ($current) {
            array_unshift($pathParts, $current->name);
            $current = $current->parent;
        }

        return 'uploads/' . implode('/', $pathParts);
    }

    /**
     * Move an asset to a different folder.
     */
    public function moveAsset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'asset_id' => 'required|exists:assets,id',
            'target_folder_id' => 'nullable|exists:folders,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $assetId = $request->input('asset_id');
        $targetFolderId = $request->input('target_folder_id');

        $asset = Asset::findOrFail($assetId);
        
        // Check write permissions
        $project = $asset->project;
        if (!$project || !$this->hasWriteAccess($project, $request->user())) {
            return response()->json(['error' => 'You do not have permission to move files in this project'], 403);
        }

        // If target folder is specified, verify it's in the same project
        if ($targetFolderId) {
            $targetFolder = Folder::find($targetFolderId);
            if ($targetFolder && $targetFolder->project_id !== $asset->project_id) {
                return response()->json(['error' => 'Cannot move files to a different project'], 403);
            }
        }

        try {
            $asset->update(['folder_id' => $targetFolderId]);
            return response()->json(['success' => true, 'message' => 'File moved successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to move file: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get ordered folders by parent_id.
     * Returns folders ordered by order column (0,1,2,...)
     */
    public function getByParent(Request $request, $parentId = null)
    {
        // Validate parent_id exists if provided
        if ($parentId !== null) {
            $parentFolder = Folder::find($parentId);
            if (!$parentFolder) {
                return response()->json(['error' => 'Parent folder not found'], 404);
            }

            // Check read access to the project
            $project = $parentFolder->project;
            if (!$project) {
                return response()->json(['error' => 'Project not found'], 404);
            }
        }

        // Get folders ordered by order column
        $folders = Folder::byParent($parentId)
            ->ordered()
            ->with(['children' => function($query) {
                $query->orderBy('order');
            }])
            ->get();

        return response()->json([
            'folders' => $folders,
            'parent_id' => $parentId
        ]);
    }

    /**
     * Reorder folders via drag-and-drop.
     * Accepts array of folder IDs in new order and updates order column.
     */
    public function reorder(Request $request)
    {
        \Log::info('Reorder request received', $request->all());

        $validator = Validator::make($request->all(), [
            'folder_ids' => 'required|array',
            'folder_ids.*' => 'required|integer|exists:folders,id',
            'parent_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            \Log::error('Validation failed', $validator->errors()->toArray());
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $folderIds = $request->input('folder_ids');
        $parentId = $request->input('parent_id');

        \Log::info('Reorder parameters', ['folder_ids' => $folderIds, 'parent_id' => $parentId]);

        // If parent_id is provided, verify it exists
        if ($parentId !== null) {
            $parentFolder = Folder::find($parentId);
            if (!$parentFolder) {
                \Log::error('Parent folder not found', ['parent_id' => $parentId]);
                return response()->json(['error' => 'Parent folder not found'], 404);
            }
        }

        // Verify all folders belong to the same parent
        foreach ($folderIds as $folderId) {
            $folder = Folder::find($folderId);
            if (!$folder) {
                \Log::error('Folder not found', ['folder_id' => $folderId]);
                return response()->json(['error' => 'Folder not found'], 404);
            }

            \Log::info('Checking folder', [
                'folder_id' => $folderId,
                'folder_parent_folder_id' => $folder->parent_folder_id,
                'requested_parent_id' => $parentId,
                'match' => $folder->parent_folder_id == $parentId
            ]);

            // Check parent_folder_id matches (both null or both same value)
            // Treat both 0 and null as root level
            $folderParent = $folder->parent_folder_id;
            $requestedParent = $parentId;

            // Normalize: treat 0 as null for root folders
            if ($folderParent === 0) $folderParent = null;
            if ($requestedParent === 0) $requestedParent = null;

            if ($folderParent != $requestedParent) {
                \Log::error('Parent mismatch', [
                    'folder_id' => $folderId,
                    'folder_parent_folder_id' => $folder->parent_folder_id,
                    'requested_parent_id' => $parentId,
                    'normalized_folder_parent' => $folderParent,
                    'normalized_requested_parent' => $requestedParent
                ]);
                return response()->json(['error' => 'All folders must belong to the same parent'], 400);
            }

            // Check write access to the project
            $project = $folder->project;
            if (!$project || !$this->hasWriteAccess($project, $request->user())) {
                \Log::error('Permission denied', ['folder_id' => $folderId]);
                return response()->json(['error' => 'You do not have permission to reorder folders in this project'], 403);
            }
        }

        try {
            \Log::info('Starting bulk update order', ['folder_ids' => $folderIds, 'parent_id' => $parentId]);
            // Bulk update order using transaction
            Folder::bulkUpdateOrder($folderIds, $parentId);

            \Log::info('Bulk update order successful');
            return response()->json([
                'success' => true,
                'message' => 'Folders reordered successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Bulk update order failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to reorder folders: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get folder tree for a project.
     */
    public function getFolderTree(Request $request, Project $project)
    {
        if (!$this->hasWriteAccess($project, $request->user())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $folders = Folder::where('project_id', $project->id)
            ->whereNull('parent_folder_id')
            ->with(['children' => function($query) {
                $query->orderBy('order')->with(['children' => function($q) {
                    $q->orderBy('order');
                }]);
            }])
            ->orderBy('order')
            ->get();

        return response()->json($folders);
    }

    /**
     * Show asset details page.
     */
    public function showAsset(Asset $asset)
    {
        // Load asset with folder, project, annotations, and versions relationships
        $asset->load(['folder', 'folder.project', 'uploadedBy', 'annotations', 'annotations.comments', 'comments', 'versions', 'currentVersion']);

        // Check if user has access to this asset
        $project = $asset->folder ? $asset->folder->project : null;
        if ($project) {
            // Check if user is project owner or collaborator
            $isOwner = $project->isOwnedBy(auth()->user());
            $isCollaborator = $project->getCollaborator(auth()->user()) !== null;

            if (!$isOwner && !$isCollaborator) {
                abort(403, 'You do not have access to this asset');
            }
        }

        return view('assets.show', compact('asset'));
    }

    /**
     * Show all assets index page.
     */
    public function indexAssets(Request $request)
    {
        $user = auth()->user();

        // Get all workspaces the user has access to
        $workspaceIds = \App\Models\WorkspaceUser::where('user_id', $user->id)
            ->where('status', 'approved')
            ->pluck('workspace_id');

        // Get all projects in those workspaces
        $projectIds = \App\Models\Project::whereIn('workspace_id', $workspaceIds)
            ->pluck('id');

        // Build query
        $query = Asset::whereIn('project_id', $projectIds)
            ->with(['folder', 'folder.project', 'uploadedBy']);

        // Handle status filter
        $status = $request->query('status');
        if ($status) {
            // Map 'pending' to 'in_review' for user convenience
            if ($status === 'pending') {
                $status = 'in_review';
            }
            $query->where('status', $status);
        }

        $assets = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('assets.index', compact('assets'));
    }

    /**
     * Fix existing assets without versions
     */
    public function fixAssetVersions()
    {
        $assetsWithoutVersions = Asset::whereNull('current_version_id')->get();

        foreach ($assetsWithoutVersions as $asset) {
            try {
                $assetVersion = AssetVersion::create([
                    'asset_id' => $asset->id,
                    'version_number' => $asset->version ?? 1.0,
                    'name' => $asset->name,
                    'file_path' => $asset->file_path,
                    'file_type' => $asset->file_type,
                    'file_size' => $asset->file_size,
                    'hash' => hash_file('sha256', storage_path('app/public/' . $asset->file_path)),
                    'status' => $asset->status ?? 'draft',
                    'uploaded_by' => $asset->uploaded_by,
                ]);

                $asset->update(['current_version_id' => $assetVersion->id]);

                \Log::info('Fixed asset version', [
                    'asset_id' => $asset->id,
                    'asset_version_id' => $assetVersion->id,
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to fix asset version', [
                    'asset_id' => $asset->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return redirect()->back()->with('success', "Fixed " . count($assetsWithoutVersions) . " assets without versions");
    }

    /**
     * Submit asset for review.
     */
    public function submitForReview(Request $request, Asset $asset)
    {
        // Check if user can submit (must be asset creator or have write access)
        $project = $asset->folder ? $asset->folder->project : null;
        if (!$project || !$this->hasWriteAccess($project, $request->user())) {
            return redirect()->back()->with('error', 'You do not have permission to submit this asset for review');
        }

        if (!$asset->submitForReview()) {
            return redirect()->back()->with('error', 'Failed to submit asset for review. Asset must be in draft status.');
        }

        // Get workflow_id from request, default to null if not provided
        $workflowId = $request->input('workflow_id');

        // Create approval assignments for project reviewers
        $this->createApprovalsForAsset($asset, $project, $workflowId);

        return redirect()->back()->with('success', 'Asset submitted for review successfully');
    }

    /**
     * Approve asset.
     */
    public function approveAsset(Request $request, Asset $asset)
    {
        // Check if user can approve using policy
        if (!auth()->user()->can('approve', $asset)) {
            return redirect()->back()->with('error', 'You are not authorized to approve this asset');
        }

        // Find the user's approval record for this asset
        $approval = $asset->approvals()
            ->where('assigned_to', auth()->id())
            ->where('status', 'pending')
            ->first();

        if (!$approval) {
            return redirect()->back()->with('error', 'No pending approval found for this asset');
        }

        // Update the approval status - this will trigger the workflow logic
        $approval->update([
            'status' => 'approved',
            'decided_at' => now(),
            'decided_by' => auth()->id()
        ]);

        return redirect()->back()->with('success', 'Approval recorded successfully');
    }

    /**
     * Reject asset.
     */
    public function rejectAsset(Request $request, Asset $asset)
    {
        // Check if user can reject using policy
        if (!auth()->user()->can('reject', $asset)) {
            return redirect()->back()->with('error', 'You are not authorized to reject this asset');
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Find the user's approval record for this asset
        $approval = $asset->approvals()
            ->where('assigned_to', auth()->id())
            ->where('status', 'pending')
            ->first();

        if (!$approval) {
            return redirect()->back()->with('error', 'No pending approval found for this asset');
        }

        // Update the approval status - this will trigger the workflow logic
        $approval->update([
            'status' => 'rejected',
            'decision_reason' => $request->input('reason') ?? '',
            'decided_at' => now(),
            'decided_by' => auth()->id()
        ]);

        // Update asset status to rejected
        $asset->update(['status' => 'rejected']);

        // Store rejection reason in asset version notes
        if ($asset->currentVersion) {
            $asset->currentVersion->update(['notes' => $request->input('reason')]);
        }

        return redirect()->back()->with('success', 'Asset rejected successfully');
    }

    /**
     * Request changes for asset.
     */
    public function requestChanges(Request $request, Asset $asset)
    {
        // Check if user can request changes using policy
        if (!auth()->user()->can('requestChanges', $asset)) {
            return redirect()->back()->with('error', 'You are not authorized to request changes for this asset');
        }

        $validator = Validator::make($request->all(), [
            'comments' => 'required|string|max:1000',
            'annotation_id' => 'nullable|exists:annotations,id',
            'annotation_x' => 'nullable|numeric',
            'annotation_y' => 'nullable|numeric',
            'annotation_width' => 'nullable|numeric',
            'annotation_height' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Find the user's approval record for this asset
        $approval = $asset->approvals()
            ->where('assigned_to', auth()->id())
            ->where('status', 'pending')
            ->first();

        if (!$approval) {
            return redirect()->back()->with('error', 'No pending approval found for this asset');
        }

        // Update the approval status - this will trigger the workflow logic
        $approval->update([
            'status' => 'changes_requested',
            'decision_reason' => $request->input('comments'),
            'decided_at' => now(),
            'decided_by' => auth()->id()
        ]);

        // Store comments in asset version notes
        if ($asset->currentVersion) {
            $asset->currentVersion->update(['notes' => $request->input('comments')]);
        }

        // Create annotation if coordinates are provided
        $annotationId = $request->input('annotation_id');
        if ($request->filled('annotation_x') && $request->filled('annotation_y')) {
            $annotation = \App\Models\Annotation::create([
                'asset_id' => $asset->id,
                'x' => $request->input('annotation_x'),
                'y' => $request->input('annotation_y'),
                'width' => $request->input('annotation_width'),
                'height' => $request->input('annotation_height'),
                'status' => 'pending',
                'created_by' => auth()->id(),
            ]);
            $annotationId = $annotation->id;
        }

        // Create a comment in the comments table for the change request
        $comment = \App\Models\Comment::create([
            'asset_id' => $asset->id,
            'user_id' => auth()->id(),
            'text' => $request->input('comments'),
            'annotation_id' => $annotationId,
            'mentioned_users' => null,
        ]);

        // Notify asset uploader about comment (if not the commenter)
        if ($asset->uploadedBy && $asset->uploadedBy->id !== auth()->id()) {
            $asset->uploadedBy->notify(new \App\Notifications\AssetCommented($asset, $comment, auth()->user()));
        }

        // If annotation was created or annotation_id was provided, update annotation status
        if ($annotationId) {
            $annotation = \App\Models\Annotation::find($annotationId);
            if ($annotation && $annotation->asset_id === $asset->id) {
                $annotation->status = 'pending';
                $annotation->save();
            }
        }

        return redirect()->back()->with('success', 'Changes requested successfully');
    }

    /**
     * Create approval assignments for an asset.
     */
    private function createApprovalsForAsset(Asset $asset, Project $project, ?int $workflowId = null): void
    {
        // Get the workflow - use provided workflow_id or get/create default
        if ($workflowId) {
            $workflow = \App\Models\Workflow::findOrFail($workflowId);
        } else {
            $workflow = \App\Models\Workflow::firstOrCreate(
                ['project_id' => $project->id],
                [
                    'name' => $project->name . ' Workflow',
                    'definition' => ['sequential' => true],
                ]
            );
        }

        // For single template workflows, use the specific approver from workflow definition
        if ($workflow && $workflow->type === 'single') {
            $steps = $workflow->getSteps();
            if (!empty($steps) && !empty($steps[0]['approvers'])) {
                $singleApproverId = $steps[0]['approvers'][0];
                try {
                    $approval = \App\Models\Approval::create([
                        'asset_id' => $asset->id,
                        'workflow_id' => $workflow->id,
                        'assigned_to' => $singleApproverId,
                        'status' => 'pending',
                        'order' => 1,
                    ]);

                    // Notify reviewer about asset ready for review (but not the uploader)
                    if ($singleApproverId !== $asset->uploaded_by) {
                        $reviewer = \App\Models\User::find($singleApproverId);
                        if ($reviewer) {
                            $reviewer->notify(new \App\Notifications\AssetReadyForReview($asset, $approval));
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error("Failed to create approval for single approver {$singleApproverId}: " . $e->getMessage());
                }
            }
            return;
        }

        // For other workflow types (sequential, parallel) or no workflow, use project reviewers
        // Get project collaborators with reviewer/admin role
        $collaborators = $project->projectCollaborators()
            ->whereIn('role', ['admin', 'reviewer'])
            ->where('status', 'approved')
            ->with('user')
            ->get();

        // Collect valid users from collaborators
        $reviewers = collect();
        foreach ($collaborators as $collaborator) {
            if ($collaborator->user) {
                $reviewers->push($collaborator->user);
            }
        }

        // Also include project owner if they exist
        if ($project->creator) {
            $reviewers->push($project->creator);
        }

        // Create approval assignments for each valid reviewer
        foreach ($reviewers->unique('id') as $index => $reviewer) {
            try {
                $approval = \App\Models\Approval::create([
                    'asset_id' => $asset->id,
                    'workflow_id' => $workflow->id,
                    'assigned_to' => $reviewer->id,
                    'status' => 'pending',
                    'order' => $index + 1,
                ]);

                // Notify reviewer about asset ready for review (but not the uploader)
                if ($reviewer->id !== $asset->uploaded_by) {
                    $reviewer->notify(new \App\Notifications\AssetReadyForReview($asset, $approval));
                }
            } catch (\Exception $e) {
                \Log::error("Failed to create approval for user {$reviewer->id}: " . $e->getMessage());
                continue;
            }
        }
    }

    /**
     * Store annotation for an asset.
     */
    public function storeAnnotation(Request $request, Asset $asset)
    {
        // Check if user can add annotation using policy
        if (!auth()->user()->can('addAnnotation', $asset)) {
            return response()->json(['error' => 'You are not authorized to add annotations to this asset'], 403);
        }

        $validator = Validator::make($request->all(), [
            'x' => 'required|numeric',
            'y' => 'required|numeric',
            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid annotation data'], 422);
        }

        $annotation = \App\Models\Annotation::create([
            'asset_id' => $asset->id,
            'x' => $request->input('x'),
            'y' => $request->input('y'),
            'width' => $request->input('width'),
            'height' => $request->input('height'),
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);

        return response()->json(['success' => true, 'annotation' => $annotation]);
    }

    /**
     * Update annotation.
     */
    public function updateAnnotation(Request $request, Asset $asset, \App\Models\Annotation $annotation)
    {
        // Check if user can update annotation
        if ($annotation->created_by !== auth()->id()) {
            return response()->json(['error' => 'You are not authorized to update this annotation'], 403);
        }

        // Check if user has access to the asset
        if (!auth()->user()->can('view', $asset)) {
            return response()->json(['error' => 'You do not have access to this asset'], 403);
        }

        $validator = Validator::make($request->all(), [
            'x' => 'nullable|numeric',
            'y' => 'nullable|numeric',
            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'status' => 'nullable|in:pending,acknowledged,resolved',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid annotation data'], 422);
        }

        $annotation->update([
            'x' => $request->input('x', $annotation->x),
            'y' => $request->input('y', $annotation->y),
            'width' => $request->input('width', $annotation->width),
            'height' => $request->input('height', $annotation->height),
            'status' => $request->input('status', $annotation->status),
        ]);

        return response()->json(['success' => true, 'annotation' => $annotation]);
    }

    /**
     * Delete annotation.
     */
    public function deleteAnnotation(Request $request, Asset $asset, \App\Models\Annotation $annotation)
    {
        // Check if user can delete annotation
        if ($annotation->created_by !== auth()->id()) {
            return response()->json(['error' => 'You are not authorized to delete this annotation'], 403);
        }

        // Check if user has access to the asset
        if (!auth()->user()->can('view', $asset)) {
            return response()->json(['error' => 'You do not have access to this asset'], 403);
        }

        $annotation->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Upload a new version of an asset.
     */
    public function uploadNewVersion(Request $request, Asset $asset)
    {
        // Check write permissions
        $project = $asset->folder ? $asset->folder->project : null;
        if (!$project || !$this->hasWriteAccess($project, $request->user())) {
            return redirect()->back()->with('error', 'You do not have permission to upload versions for this asset');
        }

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:512000', // 500MB max
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $file = $request->file('file');
            $mimeType = $file->getMimeType();
            $size = $file->getSize();

            // Validate mime type matches the original asset
            if (!in_array($mimeType, $this->allowedMimes)) {
                return redirect()->back()->with('error', "File type '{$mimeType}' not allowed");
            }

            // Validate file type matches the original asset
            $fileType = $this->getFileType($mimeType);

            \Log::info('File type validation for new version', [
                'asset_id' => $asset->id,
                'asset_name' => $asset->name,
                'asset_file_type' => $asset->file_type,
                'uploaded_mime_type' => $mimeType,
                'uploaded_file_type' => $fileType,
                'types_match' => $fileType === $asset->file_type,
            ]);

            if ($fileType !== $asset->file_type) {
                \Log::error('File type mismatch detected', [
                    'asset_file_type' => $asset->file_type,
                    'uploaded_file_type' => $fileType,
                ]);
                return redirect()->back()->with('error', 'File type must match the original asset');
            }

            // Validate size
            $maxSize = $this->maxSizes[$fileType] ?? 50 * 1024 * 1024;
            if ($size > $maxSize) {
                $maxSizeMB = $maxSize / (1024 * 1024);
                return redirect()->back()->with('error', "File size exceeds {$maxSizeMB}MB limit");
            }

            // Generate unique filename
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $hash = hash_file('sha256', $file->getPathname());

            // Store file in the same folder as the original asset
            $folderPath = dirname($asset->file_path);
            $path = $file->storeAs($folderPath, $filename, 'public');

            if (!$path) {
                return redirect()->back()->with('error', 'Failed to store file');
            }

            // Calculate new version number (increment by 0.1)
            $latestVersion = $asset->versions()->orderBy('version_number', 'desc')->first();
            $newVersionNumber = $latestVersion ? round($latestVersion->version_number + 0.1, 1) : 1.0;

            // Create new version record
            $newVersion = AssetVersion::create([
                'asset_id' => $asset->id,
                'version_number' => $newVersionNumber,
                'name' => $asset->name,
                'file_path' => $path,
                'file_type' => $fileType,
                'file_size' => $size,
                'hash' => $hash,
                'status' => 'draft',
                'uploaded_by' => auth()->id(),
            ]);

            // Update asset with new current version and version number
            $asset->update([
                'current_version_id' => $newVersion->id,
                'version' => $newVersionNumber,
                'file_path' => $path,
                'file_size' => $size,
            ]);

            // Notify project members about the new version
            if ($project) {
                $this->notifyProjectMembers(
                    $project,
                    new NewVersionUploaded($asset, $newVersion, auth()->user()),
                    [auth()->id()]
                );
            }

            return redirect()->back()->with('success', 'New version uploaded successfully');

        } catch (\Exception $e) {
            Log::error('Error uploading new version', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'asset_id' => $asset->id,
            ]);

            return redirect()->back()->with('error', 'Failed to upload new version: ' . $e->getMessage());
        }
    }

    /**
     * View a specific version of an asset.
     */
    public function viewVersion(Asset $asset, AssetVersion $version)
    {
        // Check if version belongs to the asset
        if ($version->asset_id !== $asset->id) {
            abort(404, 'Version not found for this asset');
        }

        // Check if user has access to this asset
        $project = $asset->folder ? $asset->folder->project : null;
        if ($project) {
            $isOwner = $project->isOwnedBy(auth()->user());
            $isCollaborator = $project->getCollaborator(auth()->user()) !== null;

            if (!$isOwner && !$isCollaborator) {
                abort(403, 'You do not have access to this asset');
            }
        }

        // Load asset with relationships
        $asset->load(['folder', 'folder.project', 'uploadedBy', 'annotations', 'annotations.comments', 'comments', 'versions']);

        // Override the current file path with the version's file path
        $asset->file_path = $version->file_path;
        $asset->file_size = $version->file_size;
        $asset->version = $version->version_number;

        return view('assets.show', compact('asset', 'version'));
    }

    /**
     * Notify project members about an event (excluding the actor).
     */
    private function notifyProjectMembers(Project $project, $notification, array $excludeUserIds = []): void
    {
        try {
            // Get project owner
            $membersToNotify = collect();
            
            if ($project->creator && !in_array($project->creator->id, $excludeUserIds)) {
                $membersToNotify->push($project->creator);
            }

            // Get approved collaborators
            $collaborators = ProjectCollaborator::where('project_id', $project->id)
                ->where('status', 'approved')
                ->with('user')
                ->get();

            foreach ($collaborators as $collaborator) {
                if ($collaborator->user && !in_array($collaborator->user->id, $excludeUserIds)) {
                    $membersToNotify->push($collaborator->user);
                }
            }

            // Send notifications
            foreach ($membersToNotify->unique('id') as $user) {
                $user->notify($notification);
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify project members', [
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
