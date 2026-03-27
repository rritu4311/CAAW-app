<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ __('File Manager') }}
    </h2>
</x-slot>

<!-- Asset Preview Modal -->
<x-asset-preview />

<!-- Cropper.js Library -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">File Manager</h1>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">Upload and organize your files</p>
                    </div>
                    
                    <!-- Create Folder Button -->
                    <div class="flex items-center space-x-4">
                        <form action="{{ route('folder-manager') }}" method="GET" class="inline">
                            <input type="hidden" name="toggle_folder" value="1">
                            <button type="submit" 
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                New Folder
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Breadcrumb Navigation -->
                @if(count($breadcrumb) > 0)
                    <nav class="flex items-center space-x-2 text-sm mb-6">
                        <a href="{{ route('folder-manager') }}" class="text-blue-600 hover:text-blue-800">
                            Home
                        </a>
                        @foreach($breadcrumb as $index => $crumb)
                            <span class="text-gray-400">/</span>
                            @if($index < count($breadcrumb) - 1)
                                <a href="{{ route('folder-manager', ['folder' => $crumb['path']]) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $crumb['name'] }}
                                </a>
                            @else
                                <span class="text-gray-700 font-medium">{{ $crumb['name'] }}</span>
                            @endif
                        @endforeach
                    </nav>
                @endif

                <!-- Create Folder Form -->
                @if(request('toggle_folder'))
                    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <form action="{{ route('folders.store') }}" method="POST">
                            @csrf
                            <div class="flex items-center space-x-4">
                                <input type="text" name="name" placeholder="Folder name" required
                                       class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-600 dark:text-white">
                                <input type="hidden" name="parent_folder" value="{{ $currentFolder }}">
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    Create
                                </button>
                                <a href="{{ route('folder-manager', ['folder' => $currentFolder]) }}" 
                                   class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500 transition-colors">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                @endif

                <!-- Success Message -->
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Error Messages -->
                @if($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Upload Area -->
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-4 sm:p-6 lg:p-8 text-center hover:border-blue-500 dark:hover:border-blue-400 transition-all duration-300 mb-6 relative overflow-hidden" id="dropZone">
                    <!-- Upload Progress -->
                    <div id="uploadProgress" class="hidden absolute inset-0 bg-white dark:bg-gray-800 bg-opacity-95 flex items-center justify-center z-10">
                        <div class="text-center">
                            <div class="w-16 h-16 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin mx-auto mb-4"></div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Uploading files...</p>
                            <div class="w-full max-w-xs bg-gray-200 rounded-full h-2">
                                <div id="progressBar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="uploadContent">
                        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4 transition-transform duration-300 hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <p class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Upload files to this folder</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Select files and upload them instantly</p>
                    </div>
                    
                    <form action="{{ route('folders.upload') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                        @csrf
                        <input type="hidden" name="folder" value="{{ $currentFolder }}">
                        
                        <div class="flex items-center justify-center space-x-4">
                            <input type="file" name="files[]" multiple id="fileInput"
                                   class="hidden"
                                   accept=".jpg,.jpeg,.png,.gif,.webp,.mp4,.mov,.webm,.pdf,.docx,.xlsx,.txt,.md">
                            
                            <button type="button" onclick="document.getElementById('fileInput').click()"
                                    class="px-4 sm:px-6 py-2.5 sm:py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all duration-300 cursor-pointer hover:shadow-lg transform hover:-translate-y-0.5 text-sm sm:text-base flex items-center">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                Choose Files
                            </button>
                        </div>
                        
                        <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Supported file types:</p>
                            <div class="grid grid-cols-2 gap-2 text-xs text-gray-600 dark:text-gray-400">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                                    </svg>
                                    Images (PNG, JPG, GIF, WebP)
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path>
                                    </svg>
                                    Videos (MP4, MOV, WebM)
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Documents (PDF, DOCX, XLSX)
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Text (TXT, MARKDOWN)
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Max file size: 50MB (images/docs), 500MB (videos)</p>
                        </div>
                    </form>
                </div>

                <!-- File Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-4">
                    <!-- Folders -->
                    @foreach($folders as $folder)
                        <a href="{{ route('folder-manager', ['folder' => $folder['path']]) }}" 
                           class="group bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 rounded-xl p-4 hover:shadow-lg hover:scale-105 transition-all duration-300 cursor-pointer block border border-blue-200 dark:border-blue-700">
                            <div class="flex flex-col items-center text-center">
                                <div class="w-16 h-16 bg-blue-500 rounded-lg flex items-center justify-center mb-3 group-hover:bg-blue-600 transition-colors">
                                    <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $folder['name'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Folder</p>
                            </div>
                        </a>
                    @endforeach

                    <!-- Files -->
                    @foreach($files as $file)
                        <div class="group bg-white dark:bg-gray-800 rounded-xl overflow-hidden hover:shadow-lg hover:scale-105 transition-all duration-300 border border-gray-200 dark:border-gray-700">
                            @if($file['is_image'])
                                <div class="relative h-32 cursor-pointer" onclick="openAssetPreview({{ $file['id'] }})">
                                    <img src="/assets/{{ $file['id'] }}/thumbnail/medium" alt="{{ $file['name'] }}" 
                                         class="w-full h-full object-cover hover:opacity-90 transition-opacity"
                                         onerror="this.src='{{ $file['url'] }}'">
                                    <div class="absolute inset-0 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity bg-black bg-opacity-40">
                                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </div>
                                    <div class="absolute top-2 right-2 bg-green-500 text-white text-xs px-2 py-1 rounded-full">
                                        Image
                                    </div>
                                </div>
                            @elseif($file['is_video'])
                                <div class="relative h-32 cursor-pointer" onclick="openAssetPreview({{ $file['id'] }})">
                                    <img src="/assets/{{ $file['id'] }}/thumbnail/medium" alt="{{ $file['name'] }} thumbnail"
                                         class="w-full h-full object-cover hover:opacity-90 transition-opacity"
                                         onerror="this.src='data:image/svg+xml;base64,{{ base64_encode('<svg width="200" height="128" xmlns="http://www.w3.org/2000/svg"><rect width="200" height="128" fill="%23dbeafe"/><rect width="200" height="128" fill="none" stroke="%233b82f6" stroke-width="2"/><g transform="translate(80, 44)" fill="%233b82f6"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"/></g><text x="100" y="90" text-anchor="middle" fill="%231e40af" font-family="Arial" font-size="12">Video</text></svg>') }}'">
                                    <div class="absolute inset-0 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity bg-black bg-opacity-40">
                                        <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="absolute top-2 right-2 bg-blue-500 text-white text-xs px-2 py-1 rounded-full">
                                        Video
                                    </div>
                                </div>
                            @else
                                <div class="relative h-32 cursor-pointer" onclick="openAssetPreview({{ $file['id'] }})">
                                    @if(str_contains($file['name'], '.pdf'))
                                        <img src="/assets/{{ $file['id'] }}/thumbnail/medium" alt="{{ $file['name'] }} thumbnail"
                                             class="w-full h-full object-cover hover:opacity-90 transition-opacity"
                                             onerror="this.src='data:image/svg+xml;base64,{{ base64_encode('<svg width="200" height="128" xmlns="http://www.w3.org/2000/svg"><rect width="200" height="128" fill="%23fee2e2"/><rect width="200" height="128" fill="none" stroke="%23ef4444" stroke-width="2"/><g transform="translate(80, 44)" fill="%23ef4444"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></g><text x="100" y="90" text-anchor="middle" fill="%23991b1b" font-family="Arial" font-size="12">PDF</text></svg>') }}'">
                                        <div class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full">
                                            PDF
                                        </div>
                                    @elseif(str_contains($file['name'], '.docx'))
                                        <div class="w-full h-full bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-800/30 dark:to-blue-700/30 flex items-center justify-center hover:opacity-90 transition-opacity">
                                            <div class="text-center">
                                                <svg class="w-12 h-12 text-blue-600 dark:text-blue-400 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                                                </svg>
                                                <div class="bg-blue-600 text-white text-xs px-2 py-1 rounded-full">DOCX</div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 flex items-center justify-center hover:opacity-90 transition-opacity">
                                            <div class="text-center">
                                                <svg class="w-12 h-12 text-gray-600 dark:text-gray-400 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                                                </svg>
                                                <div class="bg-gray-600 text-white text-xs px-2 py-1 rounded-full">File</div>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="absolute inset-0 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity bg-black bg-opacity-40">
                                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="p-3">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate mb-1" title="{{ $file['name'] }}">
                                    {{ $file['name'] }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">{{ $file['formatted_size'] }}</p>
                            <div class="flex justify-center space-x-1 sm:space-x-2">
                                <button onclick="openAssetPreview({{ $file['id'] }})"
                                       class="inline-flex items-center px-2 py-1 sm:px-3 sm:py-1.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors text-xs font-medium">
                                    <svg class="w-3 h-3 mr-1 transition-all duration-200 hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="3" opacity="0.3">
                                            <animate attributeName="r" values="3;5;3" dur="2s" repeatCount="indefinite"/>
                                            <animate attributeName="opacity" values="0.3;0.1;0.3" dur="2s" repeatCount="indefinite"/>
                                        </circle>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <span class="hidden sm:inline">Preview</span>
                                </button>
                                <a href="{{ $file['url'] }}" download 
                                   class="inline-flex items-center px-2 py-1 sm:px-3 sm:py-1.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-lg hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors text-xs font-medium">
                                    <svg class="w-3 h-3 mr-1 transition-transform duration-200 hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4">
                                            <animateTransform attributeName="transform" type="translate" values="0,0; 0,1; 0,0" dur="2s" repeatCount="indefinite"/>
                                        </path>
                                    </svg>
                                    <span class="hidden sm:inline">Download</span>
                                </a>
                                
                                <form action="{{ route('folders.file.delete') }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this file?')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="path" value="{{ $file['file_path'] }}">
                                    <button type="submit" 
                                            class="inline-flex items-center px-2 py-1 sm:px-3 sm:py-1.5 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors text-xs font-medium">
                                        <svg class="w-3 h-3 mr-1 transition-all duration-200 hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                <animate attributeName="stroke-opacity" values="0.6;1;0.6" dur="3s" repeatCount="indefinite"/>
                                            </path>
                                        </svg>
                                        <span class="hidden sm:inline">Delete</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
                </div>

                <!-- Empty State -->
                @if(count($files) === 0 && count($folders) === 0)
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-gray-500 dark:text-gray-400">No files in this folder</p>
                        <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Upload some files to get started</p>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
</x-app-layout>

<script>
// Drag and Drop functionality
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const uploadForm = document.getElementById('uploadForm');
    const uploadProgress = document.getElementById('uploadProgress');
    const uploadContent = document.getElementById('uploadContent');
    const progressBar = document.getElementById('progressBar');

    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });

    // Highlight drop zone when item is dragged over it
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });

    // Handle dropped files
    dropZone.addEventListener('drop', handleDrop, false);

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    function highlight() {
        dropZone.classList.add('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
        dropZone.classList.remove('border-gray-300', 'dark:border-gray-600');
    }

    function unhighlight() {
        dropZone.classList.remove('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
        dropZone.classList.add('border-gray-300', 'dark:border-gray-600');
    }

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    }

    function handleFiles(files) {
        fileInput.files = files;
        uploadWithProgress();
    }

    function uploadWithProgress() {
        // Show progress indicator
        uploadProgress.classList.remove('hidden');
        uploadContent.classList.add('opacity-50');

        // Simulate progress (in real app, this would be based on actual upload progress)
        let progress = 0;
        const interval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress > 90) progress = 90;
            progressBar.style.width = progress + '%';
        }, 200);

        // Submit form
        const formData = new FormData(uploadForm);
        const xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                progressBar.style.width = percentComplete + '%';
            }
        });

        xhr.addEventListener('load', function() {
            clearInterval(interval);
            progressBar.style.width = '100%';
            
            // Redirect to refresh the page with new files
            setTimeout(() => {
                window.location.reload();
            }, 500);
        });

        xhr.addEventListener('error', function() {
            clearInterval(interval);
            uploadProgress.classList.add('hidden');
            uploadContent.classList.remove('opacity-50');
            alert('Upload failed. Please try again.');
        });

        xhr.open('POST', uploadForm.action);
        xhr.send(formData);
    }

    // File input change handler
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            uploadWithProgress();
        }
    });

    // Click to upload
    dropZone.addEventListener('click', function(e) {
        if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'BUTTON') {
            fileInput.click();
        }
    });
});
</script>
