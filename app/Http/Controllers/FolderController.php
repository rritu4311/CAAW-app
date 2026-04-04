<?php

namespace App\Http\Controllers;
use App\Models\Folder;
use App\Models\Asset;
use App\Models\Project;
use App\Models\WorkspaceUser;
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
        
        if (!$project || !$this->hasWriteAccess($project, $request->user())) {
            return redirect()->back()
                ->withErrors(['permission' => 'You do not have permission to create folders in this project'])
                ->withInput();
        }

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('folders')
                    ->where('project_id', $request->input('project_id'))
                    ->where('parent_folder_id', $request->input('parent_folder_id')),
            ],
            'parent_folder_id' => 'nullable|exists:folders,id',
            'project_id' => 'required|exists:projects,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $folder = Folder::create([
                'name' => $request->input('name'),
                'project_id' => $request->input('project_id'),
                'parent_folder_id' => $request->input('parent_folder_id'),
                'order' => 0,
            ]);

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
        // Load folder with children, parent, project, and assets hierarchy
        $folder->load(['children', 'parent', 'project', 'assets']);
        
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

        // Store parent information before deletion
        $parentFolder = $folder->parent;
        $project = $folder->project;
        
        $this->deleteFolderRecursive($folder);
        
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

            // Delete from database first
            $asset = Asset::where('file_path', $path)->first();
            if ($asset) {
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

            // Create folder path
            $folderPath = $folder === 'root' ? 'uploads' : 'uploads/' . trim($folder, '/');
            
            // Ensure folder exists
            if (!Storage::disk('public')->exists($folderPath)) {
                Storage::disk('public')->makeDirectory($folderPath);
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

            // Use the provided folderId or get it from folder name
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
            ]);

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
}
