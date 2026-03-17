<?php

namespace App\Http\Controllers;
// use App\Models\Asset;
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

            return [
                'success' => true,
                'file' => [
                    'filename' => $filename,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $mimeType,
                    'size' => $size,
                    'path' => $path,
                    'hash' => $hash,
                    'folder' => $folder,
                    'url' => Storage::url($path),
                    'is_image' => str_starts_with($mimeType, 'image/'),
                    'is_video' => str_starts_with($mimeType, 'video/'),
                    'is_document' => in_array($mimeType, [
                        'application/pdf',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ]),
                    'is_text' => str_starts_with($mimeType, 'text/') || $mimeType === 'text/markdown',
                    'formatted_size' => $this->formatFileSize($size),
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
        
        return 'document';
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
            
            if (Storage::disk('public')->exists($folderPath)) {
                // Get files
                $fileItems = Storage::disk('public')->files($folderPath);
                foreach ($fileItems as $file) {
                    $fullPath = storage_path('app/public/' . $file);
                    if (file_exists($fullPath)) {
                        $mimeType = mime_content_type($fullPath);
                        $size = filesize($fullPath);
                        
                        $files[] = [
                            'filename' => basename($file),
                            'original_name' => basename($file),
                            'mime_type' => $mimeType,
                            'size' => $size,
                            'path' => $file,
                            'url' => Storage::url($file),
                            'is_image' => str_starts_with($mimeType, 'image/'),
                            'is_video' => str_starts_with($mimeType, 'video/'),
                            'is_document' => in_array($mimeType, [
                                'application/pdf',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            ]),
                            'is_text' => str_starts_with($mimeType, 'text/') || $mimeType === 'text/markdown',
                            'formatted_size' => $this->formatFileSize($size),
                            'modified_at' => date('Y-m-d H:i:s', filemtime($fullPath)),
                        ];
                    }
                }
                
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

            Storage::disk('public')->delete($path);

            Log::info('File deleted', [
                'user_id' => auth()->id(),
                'file_path' => $path,
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully',
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

    public function deleteFolder(Request $request): JsonResponse
    {
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
}
