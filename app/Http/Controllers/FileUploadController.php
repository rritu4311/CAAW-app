<?php

namespace App\Http\Controllers;
use App\Models\Asset;
use App\Models\AssetVersion;
use App\Models\Folder;
use App\Models\Project;
use App\Models\WorkspaceUser;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class FileUploadController extends Controller
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

    public function upload(Request $request): JsonResponse
    {
        // Check write permissions
        $folderId = $request->input('folder');
        $project = $this->getProjectFromFolder($folderId);
        
        if (!$project || !$this->hasWriteAccess($project, $request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to upload files to this project',
            ], 403);
        }

        Log::info('File upload started', [
            'user_id' => auth()->id(),
            'files_count' => $request->hasFile('files') ? count($request->file('files')) : 0,
            'folder' => $request->input('folder', 'root'),
            'timestamp' => now()->toISOString()
        ]);

        $validator = Validator::make($request->all(), [
            'files' => 'required|array|max:10',
            'files.*' => 'required|file|max:512000', // 500MB max per file
            'folder' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            Log::error('File upload validation failed', [
                'user_id' => auth()->id(),
                'errors' => $validator->errors()->toArray(),
                'timestamp' => now()->toISOString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $uploadedFiles = [];
        $errors = [];
        $folder = $request->input('folder', 'root');

        foreach ($request->file('files') as $index => $file) {
            try {
                $result = $this->processFile($file, $folder);
                if ($result['success']) {
                    $uploadedFiles[] = $result['file'];
                    Log::info('File uploaded successfully', [
                        'user_id' => auth()->id(),
                        'filename' => $result['file']['filename'],
                        'original_name' => $result['file']['original_name'],
                        'size' => $result['file']['size'],
                        'folder' => $folder,
                        'timestamp' => now()->toISOString()
                    ]);
                } else {
                    $errors["files.{$index}"] = $result['error'];
                    Log::error('File upload failed', [
                        'user_id' => auth()->id(),
                        'original_name' => $file->getClientOriginalName(),
                        'error' => $result['error'],
                        'folder' => $folder,
                        'timestamp' => now()->toISOString()
                    ]);
                }
            } catch (\Exception $e) {
                $error = 'Upload failed: ' . $e->getMessage();
                $errors["files.{$index}"] = $error;
                Log::error('File upload exception', [
                    'user_id' => auth()->id(),
                    'original_name' => $file->getClientOriginalName(),
                    'error' => $error,
                    'trace' => $e->getTraceAsString(),
                    'folder' => $folder,
                    'timestamp' => now()->toISOString()
                ]);
            }
        }

        Log::info('File upload batch completed', [
            'user_id' => auth()->id(),
            'uploaded_count' => count($uploadedFiles),
            'error_count' => count($errors),
            'folder' => $folder,
            'timestamp' => now()->toISOString()
        ]);

        return response()->json([
            'success' => count($uploadedFiles) > 0,
            'files' => $uploadedFiles,
            'errors' => $errors,
            'uploaded_count' => count($uploadedFiles),
            'total_count' => count($request->file('files')),
        ]);
    }

    private function processFile($file, string $folder): array
    {
        try {
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
                Log::info('Folder created', [
                    'folder_path' => $folderPath,
                    'timestamp' => now()->toISOString()
                ]);
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

            // Save to database
            $asset = Asset::create([
                'name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $this->getFileType($mimeType),
                'file_size' => $size,
                'folder_id' => $folderId ? (int) $folderId : null,
                'project_id' => $this->getProjectIdFromFolder($folderId),
                'uploaded_by' => auth()->id(),
                'version' => 1.0,
            ]);

            Log::info('Asset created', [
                'asset_id' => $asset->id,
                'asset_name' => $asset->name,
                'uploaded_by' => $asset->uploaded_by,
                'version' => $asset->version,
                'current_version_id' => $asset->current_version_id,
            ]);

            try {
                // Create initial version record
                $assetVersion = AssetVersion::create([
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

                Log::info('AssetVersion created successfully', [
                    'asset_version_id' => $assetVersion->id,
                    'asset_id' => $assetVersion->asset_id,
                    'version_number' => $assetVersion->version_number,
                    'uploaded_by' => $assetVersion->uploaded_by,
                    'hash' => $assetVersion->hash,
                ]);

                // Update asset with current version ID
                $asset->update(['current_version_id' => $assetVersion->id]);

                Log::info('Asset updated with current_version_id', [
                    'asset_id' => $asset->id,
                    'current_version_id' => $asset->current_version_id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create AssetVersion', [
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

    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function listFiles(Request $request): JsonResponse
    {
        $folder = $request->input('folder', 'root');
        $folderPath = $folder === 'root' ? 'uploads' : 'uploads/' . trim($folder, '/');
        
        Log::info('Listing files', [
            'user_id' => auth()->id(),
            'folder' => $folder,
            'folder_path' => $folderPath,
            'timestamp' => now()->toISOString()
        ]);

        try {
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
                    ->with('folder')
                    ->get();
                
                $files = $assets->map(function ($asset) {
                    return [
                        'id' => $asset->id,
                        'name' => $asset->name,
                        'file_path' => $asset->file_path,
                        'file_type' => $asset->file_type,
                        'file_size' => $asset->file_size,
                        'path' => $asset->file_path,
                        'url' => Storage::url($asset->file_path),
                        'is_image' => $asset->file_type === 'image',
                        'is_video' => $asset->file_type === 'video',
                        'is_document' => in_array($asset->file_type, ['pdf', 'doc']),
                        'is_text' => $asset->file_type === 'text',
                        'formatted_size' => $asset->formatted_size,
                        'created_at' => $asset->created_at->format('Y-m-d H:i:s'),
                    ];
                });
            }
            
            // Get folders from file system (for now, until we have full folder system)
            if (Storage::disk('public')->exists($folderPath)) {
                // Get folders
                $folderItems = Storage::disk('public')->directories($folderPath);
                foreach ($folderItems as $dir) {
                    $folders[] = [
                        'name' => basename($dir),
                        'path' => $dir,
                        'parent_folder' => $folder,
                    ];
                }
            }

            Log::info('Files listed successfully', [
                'user_id' => auth()->id(),
                'folder' => $folder,
                'files_count' => count($files),
                'folders_count' => count($folders),
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => true,
                'files' => $files,
                'folders' => $folders,
                'current_folder' => $folder,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error listing files', [
                'user_id' => auth()->id(),
                'folder' => $folder,
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to list files',
            ], 500);
        }
    }

    public function createFolder(Request $request): JsonResponse
    {
        // Check write permissions
        $folderId = $request->input('parent_folder');
        $project = $this->getProjectFromFolder($folderId);
        
        if (!$project || !$this->hasWriteAccess($project, $request->user())) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to create folders in this project',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'parent_folder' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $name = $request->input('name');
        $parentFolder = $request->input('parent_folder', 'root');

        // Check for duplicate folder name in database
        $parentFolderId = null;
        if ($parentFolder !== 'root') {
            $parentFolderModel = Folder::where('name', $parentFolder)->first();
            $parentFolderId = $parentFolderModel ? $parentFolderModel->id : null;
        }

        $existingFolder = Folder::where('name', $name)
            ->where('parent_folder_id', $parentFolderId)
            ->first();

        if ($existingFolder) {
            return response()->json([
                'success' => false,
                'error' => "Folder '{$name}' already exists in this location",
            ], 422);
        }
        
        $folderPath = $parentFolder === 'root' 
            ? 'uploads/' . $name 
            : 'uploads/' . trim($parentFolder, '/') . '/' . $name;

        try {
            if (Storage::disk('public')->exists($folderPath)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Folder already exists',
                ], 422);
            }

            Storage::disk('public')->makeDirectory($folderPath);

            Log::info('Folder created', [
                'user_id' => auth()->id(),
                'folder_name' => $name,
                'parent_folder' => $parentFolder,
                'folder_path' => $folderPath,
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => true,
                'folder' => [
                    'name' => $name,
                    'path' => $folderPath,
                    'parent_folder' => $parentFolder,
                ],
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error creating folder', [
                'user_id' => auth()->id(),
                'folder_name' => $name,
                'parent_folder' => $parentFolder,
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to create folder',
            ], 500);
        }
    }

    public function deleteFile(Request $request): JsonResponse
    {
        // Check write permissions
        $path = $request->input('path');
        $asset = Asset::where('path', $path)->first();
        
        if ($asset && $asset->project_id) {
            $project = Project::find($asset->project_id);
            if ($project && !$this->hasWriteAccess($project, $request->user())) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to delete files in this project',
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'path' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $path = $request->input('path');

        try {
            if (!Storage::disk('public')->exists($path)) {
                return response()->json([
                    'success' => false,
                    'error' => 'File not found',
                ], 404);
            }

            // Delete from database first
            $asset = Asset::where('path', $path)->first();
            if ($asset) {
                $asset->delete();
            }

            // Delete from file system
            Storage::disk('public')->delete($path);

            Log::info('File deleted', [
                'user_id' => auth()->id(),
                'file_path' => $path,
                'asset_id' => $asset ? $asset->id : null,
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully ',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deleting file', [
                'user_id' => auth()->id(),
                'file_path' => $path,
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete file',
            ], 500);
        }
    }

    public function fileManager(Request $request)
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
                ->with('folder')
                ->get();
            
            $files = $assets->map(function ($asset) {
                return [
                    'id' => $asset->id,
                    'name' => $asset->name,
                    'file_path' => $asset->file_path,
                    'file_type' => $asset->file_type,
                    'file_size' => $asset->file_size,
                    'path' => $asset->file_path,
                    'url' => Storage::url($asset->file_path),
                    'is_image' => $asset->file_type === 'image',
                    'is_video' => $asset->file_type === 'video',
                    'is_document' => in_array($asset->file_type, ['pdf', 'doc']),
                    'is_text' => $asset->file_type === 'text',
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

    public function uploadFiles(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'files' => 'required|array|max:10',
            'files.*' => 'required|file|max:512000', // 500MB max per file
            'folder' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $uploadedFiles = [];
        $errors = [];
        $folder = $request->input('folder', 'root');

        foreach ($request->file('files') as $index => $file) {
            try {
                $result = $this->processFile($file, $folder);
                if ($result['success']) {
                    $uploadedFiles[] = $result['file'];
                } else {
                    $errors["files.{$index}"] = $result['error'];
                }
            } catch (\Exception $e) {
                $errors["files.{$index}"] = 'Upload failed: ' . $e->getMessage();
            }
        }

        if (count($uploadedFiles) > 0) {
            return redirect()->route('file-manager', ['folder' => $folder])
                ->with('success', "Successfully uploaded " . count($uploadedFiles) . " files");
        } else {
            return redirect()->back()
                ->withErrors(['upload' => 'No files were uploaded successfully'])
                ->withInput();
        }
    }

    public function createNewFolder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'parent_folder' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $name = $request->input('name');
        $parentFolder = $request->input('parent_folder', 'root');

        // Check for duplicate folder name in database
        $parentFolderId = null;
        if ($parentFolder !== 'root') {
            $parentFolderModel = Folder::where('name', $parentFolder)->first();
            $parentFolderId = $parentFolderModel ? $parentFolderModel->id : null;
        }

        $existingFolder = Folder::where('name', $name)
            ->where('parent_folder_id', $parentFolderId)
            ->first();

        if ($existingFolder) {
            return redirect()->back()
                ->withErrors(['name' => "Folder '{$name}' already exists in this location"])
                ->withInput();
        }
        
        $folderPath = $parentFolder === 'root' 
            ? 'uploads/' . $name 
            : 'uploads/' . trim($parentFolder, '/') . '/' . $name;

        try {
            if (Storage::disk('public')->exists($folderPath)) {
                return redirect()->back()
                    ->withErrors(['name' => 'Folder already exists'])
                    ->withInput();
            }

            Storage::disk('public')->makeDirectory($folderPath);

            return redirect()->route('file-manager', ['folder' => $parentFolder])
                ->with('success', 'Folder created successfully');
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['create' => 'Failed to create folder'])
                ->withInput();
        }
    }

    public function deleteAssetFile(Request $request)
    {
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

    public function deleteFolder(Request $request): JsonResponse
    {
        // Check write permissions
        $path = $request->input('path');
        $project = $this->getProjectFromPath($path);
        
        if (!$project || !$this->hasWriteAccess($project, $request->user())) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to delete folders in this project',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'path' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $path = $request->input('path');

        try {
            if (!Storage::disk('public')->exists($path)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Folder not found',
                ], 404);
            }

            // Check if folder has content (files or subfolders)
            $files = Storage::disk('public')->files($path);
            $directories = Storage::disk('public')->directories($path);

            if (count($files) > 0 || count($directories) > 0) {
                $message = [];
                if (count($files) > 0) {
                    $message[] = count($files) . ' file(s)';
                }
                if (count($directories) > 0) {
                    $message[] = count($directories) . ' subfolder(s)';
                }

                return response()->json([
                    'success' => false,
                    'error' => 'Cannot delete folder: It contains ' . implode(' and ', $message) . '. Please delete the content first.',
                ], 422);
            }

            Storage::disk('public')->deleteDirectory($path);

            Log::info('Folder deleted', [
                'user_id' => auth()->id(),
                'folder_path' => $path,
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Folder deleted successfully',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deleting folder', [
                'user_id' => auth()->id(),
                'folder_path' => $path,
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete folder',
            ], 500);
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

    /**
     * Get project from folder ID.
     */
    private function getProjectFromFolder($folderId): ?Project
    {
        if (is_numeric($folderId)) {
            $folder = Folder::find($folderId);
            if ($folder) {
                return $folder->project;
            }
        }
        return null;
    }

    /**
     * Get project from path.
     */
    private function getProjectFromPath(string $path): ?Project
    {
        // Extract project info from path if possible
        // Path format: uploads/{folder_name}/...
        $parts = explode('/', $path);
        if (count($parts) >= 2) {
            $folderName = $parts[1];
            $folder = Folder::where('name', $folderName)->first();
            if ($folder) {
                return $folder->project;
            }
        }
        return null;
    }
}
