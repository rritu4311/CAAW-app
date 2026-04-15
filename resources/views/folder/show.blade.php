<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Folder') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white dark:bg-gray-800">

                    <!-- Back Button -->
                    <div class="mb-4">
                        @if($folder->parent)
                            <a href="{{ route('folders.show', $folder->parent->id) }}"
                               class="inline-flex items-center text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Back to {{ $folder->parent->name }}
                            </a>
                        @else
                            <a href="{{ route('projects.show', $folder->project->id) }}"
                               class="inline-flex items-center text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Back to {{ $folder->project->name }}
                            </a>
                        @endif
                    </div>

                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Breadcrumb Navigation -->
                    <nav class="flex items-center space-x-2 text-sm mb-6">
                        <a href="{{ route('projects.show', $folder->project->id) }}" class="text-blue-600 hover:text-blue-800">
                            {{ $folder->project->name }}
                        </a>
                        <span class="text-gray-400">/</span>
                        @foreach($breadcrumbs as $index => $breadcrumb)
                            @if($index < count($breadcrumbs) - 1)
                                <a href="{{ route('folders.show', $breadcrumb->id) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $breadcrumb->name }}
                                </a>
                                <span class="text-gray-400">/</span>
                            @else
                                <span class="text-gray-700 font-medium">{{ $breadcrumb->name }}</span>
                            @endif
                        @endforeach
                    </nav>

                    <!-- Folder Header -->
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h1 class="text-3xl font-bold mb-2 flex items-center">
                                <svg class="w-8 h-8 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                </svg>
                                {{ $folder->name }}
                            </h1>
                            <p class="text-gray-600">
                                Created: {{ $folder->created_at->format('M d, Y') }} | 
                                {{ $folder->children->count() }} subfolder(s)
                            </p>
                            @if($readOnly)
                                <span class="mt-2 px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium inline-block">Read-Only (Workspace Admin)</span>
                            @endif
                        </div>
                        
                        @if(!$readOnly)
                        <!-- Actions -->
                        <div class="flex space-x-2">
                            <!-- Create Subfolder Button -->
                            <a href="#" onclick="openModal('createFolderModal{{ $folder->id }}')" 
                               class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors inline-block">
                                Create Subfolder
                            </a>
                            
                            <!-- Delete Folder Button -->
                            <form action="{{ route('folders.destroy', $folder->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                                    Delete Folder
                                </button>
                            </form>
                            
                        </div>
                        @endif
                    </div>

                    <!-- Success Message -->
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Create Subfolder Form -->
                    @if(!$readOnly && request('create_subfolder'))
                        <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <form action="{{ route('folders.store') }}" method="POST">
                                @csrf
                                <div class="flex items-center space-x-4">
                                    <input type="text" name="name" placeholder="Subfolder name" required
                                           class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-600 dark:text-white">
                                    <input type="hidden" name="parent_folder_id" value="{{ $folder->id }}">
                                    <input type="hidden" name="project_id" value="{{ $folder->project->id }}">
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                        Create
                                    </button>
                                    <a href="{{ route('folders.show', $folder->id) }}" 
                                       class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500 transition-colors">
                                        Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    @endif

                    <!-- File Upload Section -->
                    @if(!$readOnly)
                    <div id="dropZone" class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 hover:border-blue-500 dark:hover:border-blue-400 transition-colors mb-6 cursor-pointer">
                        <div class="text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Upload Files to "{{ $folder->name }}"</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Select files and upload them to this folder</p>
                            
                            <form action="{{ route('folders.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4" id="uploadForm">
                                @csrf
                                <input type="hidden" name="folder" value="{{ $folder->id }}">
                                
                                <!-- File Input -->
                                <div class="flex flex-col items-center space-y-4">
                                    <div class="w-full max-w-md">
                                        <input type="file" 
                                               id="fileInput"
                                               name="files[]" 
                                               multiple 
                                               accept=".jpg,.jpeg,.png,.gif,.webp,.mp4,.mov,.webm,.pdf,.docx,.xlsx,.txt,.md"
                                               class="hidden">
                                        
                                        <button type="button" onclick="document.getElementById('fileInput').click()"
                                                class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium flex items-center justify-center pointer-events-auto">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                            </svg>
                                            Choose Files
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- File Type Information -->
                                <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Supported Files & Limits:</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs text-gray-600 dark:text-gray-400">
                                        <div>📷 Images (PNG, JPG, GIF, WebP) - Max 50MB</div>
                                        <div>🎥 Videos (MP4, MOV, WebM) - Max 500MB</div>
                                        <div>📄 Documents (PDF, DOCX, XLSX) - Max 50MB</div>
                                        <div>📝 Text files (TXT, MARKDOWN) - Max 50MB</div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif

                    <!-- Folder Contents -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h2 class="text-xl font-semibold mb-4 text-gray-800">Folder Contents</h2>
                        
                        @if($folder->children->count() > 0)
                            <ul class="space-y-2">
                                @foreach($folder->children as $child)
                                    <li class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                            </svg>
                                            <a href="{{ route('folders.show', $child->id) }}" class="font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 hover:underline">
                                                {{ $child->name }}
                                            </a>
                                        </div>
                                        
                                        @if(!$readOnly)
                                        <div class="flex items-center space-x-2">
                                            <button onclick="openEditFolderModal({{ $child->id }}, '{{ $child->name }}')" 
                                                    class="p-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700" 
                                                    title="Edit Folder">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            
                                            <form action="{{ route('folders.destroy', $child->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this folder and all its contents?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1 text-xs bg-red-600 text-white rounded hover:bg-red-700" 
                                                        title="Delete Folder">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                            
                                        </div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="text-center py-12">
                                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                </svg>
                                <p class="text-gray-500 text-lg">No subfolders yet</p>
                                <p class="text-gray-400">Create your first subfolder using the button above</p>
                            </div>
                        @endif
                        
                        <!-- Files Section -->
                        @if($folder->assets->count() > 0)
                            <h3 class="text-lg font-semibold mb-3 text-gray-700">Files</h3>
                            <div class="space-y-2">
                                @foreach($folder->assets as $asset)
                                    <div class="flex items-center p-3 bg-white border rounded-lg hover:shadow-md transition-shadow">
                                        <!-- Icon/Thumbnail - Canvas API -->
                                        <div class="flex-shrink-0 w-12 h-12 mr-6">
                                            @if($asset->file_type === 'image')
                                                <canvas data-image-src="{{ Storage::url($asset->file_path) }}" 
                                                        data-file-name="{{ $asset->name }}"
                                                        class="folder-image-canvas w-12 h-12 rounded-lg cursor-pointer"
                                                        width="48" height="48"></canvas>
                                            @elseif($asset->file_type === 'video')
                                                <canvas data-video-src="{{ Storage::url($asset->file_path) }}"
                                                        data-video-info="true"
                                                        class="folder-video-canvas w-12 h-12 rounded-lg"
                                                        width="48" height="48"></canvas>
                                            @elseif($asset->file_type === 'pdf')
                                                <canvas data-pdf-src="{{ Storage::url($asset->file_path) }}"
                                                        data-pdf-name="{{ $asset->name }}"
                                                        class="folder-pdf-canvas w-12 h-12 rounded-lg"
                                                        width="48" height="48"></canvas>
                                            @elseif($asset->file_type === 'doc' || $asset->file_type === 'docx')
                                                <canvas data-doc-name="{{ $asset->name }}"
                                                        data-file-type="{{ $asset->file_type }}"
                                                        class="folder-doc-canvas w-12 h-12 rounded-lg"
                                                        width="48" height="48"></canvas>
                                            @elseif($asset->file_type === 'xlsx' || $asset->file_type === 'xls' || $asset->file_type === 'csv')
                                                <canvas data-doc-name="{{ $asset->name }}"
                                                        data-file-type="{{ $asset->file_type }}"
                                                        class="folder-excel-canvas w-12 h-12 rounded-lg"
                                                        width="48" height="48"></canvas>
                                            @elseif($asset->file_type === 'txt' || $asset->file_type === 'md' || $asset->file_type === 'markdown')
                                                <canvas data-doc-name="{{ $asset->name }}"
                                                        data-file-type="{{ $asset->file_type }}"
                                                        class="folder-text-canvas w-12 h-12 rounded-lg"
                                                        width="48" height="48"></canvas>
                                            @else
                                                <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <!-- File Info -->
                                        <div class="flex-1 min-w-0 mr-4">
                                            <h4 class="text-sm font-semibold text-gray-800 truncate" title="{{ $asset->name }}">{{ $asset->name }}</h4>
                                            <p class="text-xs text-gray-500">{{ $asset->formatted_size }} • {{ strtoupper($asset->file_type) }}</p>
                                            @if($asset->file_type === 'video')
                                                <div class="video-metadata text-xs text-gray-400 flex items-center gap-2 mt-1" data-video-src="{{ Storage::url($asset->file_path) }}">
                                                    <span class="video-duration flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        <span class="duration-value">--:--</span>
                                                    </span>
                                                    <span class="w-px h-3 bg-gray-300"></span>
                                                    <span class="video-resolution flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                        </svg>
                                                        <span class="resolution-value">--x--</span>
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <!-- Actions -->
                                        <div class="flex items-center gap-2 flex-shrink-0">
                                            @if(in_array($asset->file_type, ['image', 'video', 'pdf', 'doc', 'docx', 'xlsx', 'xls', 'csv', 'txt', 'md', 'markdown']))
                                                <a href="{{ route('assets.show', $asset->id) }}" 
                                                    class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded transition-colors">View</a>
                                            @endif
                                            <a href="{{ Storage::url($asset->file_path) }}" download="{{ $asset->name }}" 
                                                class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-medium rounded transition-colors">Download</a>
                                            
                                            @if(!$readOnly)
                                                <!-- Move Asset Button -->
                                                <button type="button" 
                                                    onclick="openMoveAssetModal({{ $asset->id }}, '{{ $asset->name }}', {{ $folder->project->id }}, {{ $folder->id ?: 'null' }})"
                                                    class="px-3 py-1.5 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 text-xs font-medium rounded transition-colors flex items-center"
                                                    title="Move File">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                                    </svg>
                                                    Move
                                                </button>

                                                <form action="{{ route('folders.file.delete') }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="path" value="{{ $asset->file_path }}">
                                                    <button type="submit" 
                                                        class="px-3 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium rounded transition-colors">
                                                        Delete
                                                    </button>
                                                </form>
                                                
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>

    @if(!$readOnly)
    <!-- Create Subfolder Modal -->
    <x-create-folder-modal :projectId="$folder->project->id" :parentFolderId="$folder->id" :error="session('error')" />
    
    <!-- Edit Folder Modal -->
    <x-edit-folder-modal />
    @endif

    <!-- File Viewer Component (Laravel Blade) -->
    <x-file-viewer :assets="$folder->assets" />

    <!-- Image Crop Modal -->
    <div id="cropModal" class="fixed inset-0 z-50 hidden" aria-labelledby="crop-modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity" onclick="closeCropModal()"></div>

        <!-- Modal Panel -->
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-2 text-center sm:p-3">
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:w-full sm:max-w-xl">
                    <!-- Header -->
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-between items-center border-b">
                        <h3 class="text-lg font-semibold text-gray-900" id="crop-modal-title">Crop Image</h3>
                        <button onclick="closeCropModal()" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Cropper Container -->
                    <div class="p-3 sm:p-4">
                        <div class="relative max-h-[35vh] overflow-hidden bg-gray-100 rounded-lg">
                            <img id="cropperImage" src="" alt="Image to crop" class="max-w-full block max-h-[35vh]">
                        </div>

                        <!-- Aspect Ratio Controls -->
                        <div class="mt-4 flex flex-wrap justify-center gap-2">
                            <button onclick="setAspectRatio(NaN)" class="aspect-btn px-3 py-1.5 text-sm font-medium rounded-md bg-blue-600 text-white hover:bg-blue-700 transition-colors" data-ratio="free">
                                Free
                            </button>
                            <button onclick="setAspectRatio(1)" class="aspect-btn px-3 py-1.5 text-sm font-medium rounded-md bg-gray-200 text-gray-700 hover:bg-gray-300 transition-colors" data-ratio="1">
                                1:1
                            </button>
                            <button onclick="setAspectRatio(16/9)" class="aspect-btn px-3 py-1.5 text-sm font-medium rounded-md bg-gray-200 text-gray-700 hover:bg-gray-300 transition-colors" data-ratio="16/9">
                                16:9
                            </button>
                            <button onclick="setAspectRatio(4/3)" class="aspect-btn px-3 py-1.5 text-sm font-medium rounded-md bg-gray-200 text-gray-700 hover:bg-gray-300 transition-colors" data-ratio="4/3">
                                4:3
                            </button>
                            <button onclick="setAspectRatio(3/2)" class="aspect-btn px-3 py-1.5 text-sm font-medium rounded-md bg-gray-200 text-gray-700 hover:bg-gray-300 transition-colors" data-ratio="3/2">
                                3:2
                            </button>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-wrap justify-between items-center gap-3 border-t">
                        <div class="flex gap-2">
                            <button onclick="cropper.rotate(-90)" class="p-2 text-gray-600 hover:bg-gray-200 rounded-lg transition-colors" title="Rotate Left">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                </svg>
                            </button>
                            <button onclick="cropper.rotate(90)" class="p-2 text-gray-600 hover:bg-gray-200 rounded-lg transition-colors" title="Rotate Right">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6" />
                                </svg>
                            </button>
                            <button onclick="cropper.reset()" class="p-2 text-gray-600 hover:bg-gray-200 rounded-lg transition-colors" title="Reset">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </button>
                            <button onclick="cropper.scaleX(-cropper.getData().scaleX || -1)" class="p-2 text-gray-600 hover:bg-gray-200 rounded-lg transition-colors" title="Flip Horizontal">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                            </button>
                            <button onclick="cropper.scaleY(-cropper.getData().scaleY || -1)" class="p-2 text-gray-600 hover:bg-gray-200 rounded-lg transition-colors" title="Flip Vertical">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="transform: rotate(90deg)">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                            </button>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="closeCropModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Cancel
                            </button>
                            <button onclick="downloadCroppedImage()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Download Crop
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Move Asset Modal -->
    <div id="moveAssetModal" class="fixed inset-0 z-50 hidden" aria-labelledby="move-asset-modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm transition-opacity duration-300" onclick="closeMoveAssetModal()"></div>

        <!-- Modal Panel -->
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center">
                <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all w-full max-w-xs">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-4 py-3 flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <div class="bg-white/20 p-1.5 rounded-lg">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                </svg>
                            </div>
                            <h3 class="text-base font-semibold text-white" id="move-asset-modal-title">Move File</h3>
                        </div>
                        <button onclick="closeMoveAssetModal()" class="text-white/80 hover:text-white focus:outline-none transition-colors rounded-lg p-1 hover:bg-white/10">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-4">
                        <div class="mb-3 p-2 bg-indigo-50 rounded-lg border border-indigo-100">
                            <p class="text-xs text-gray-600">
                                Moving: <span id="moveAssetFileName" class="font-semibold text-indigo-700"></span>
                            </p>
                        </div>
                        
                        <!-- Search Input -->
                        <div class="mb-3">
                            <div class="relative">
                                <svg class="absolute left-2.5 top-1/2 transform -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input type="text" id="folderSearchInput" placeholder="Search folders..." 
                                    class="w-full pl-8 pr-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-xs"
                                    oninput="filterFolders(this.value)">
                            </div>
                        </div>
                        
                        <!-- Folders List -->
                        <div id="moveAssetFolders" class="border border-gray-200 rounded-lg max-h-60 overflow-y-auto bg-gray-50 custom-scrollbar">
                            <div class="px-4 py-8 text-center text-xs text-gray-400">
                                <svg class="animate-spin h-5 w-5 mx-auto mb-2 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <p class="font-medium">Loading folders...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="bg-gray-50 px-4 py-3 flex justify-end gap-2 border-t border-gray-200">
                        <button onclick="closeMoveAssetModal()" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all font-medium text-xs focus:ring-2 focus:ring-gray-200 focus:outline-none">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #c7c7c7;
        border-radius: 3px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // File input auto-upload
    const fileInput = document.getElementById('fileInput');
    const uploadForm = document.getElementById('uploadForm');
    
    if (fileInput && uploadForm) {
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                uploadForm.submit();
            }
        });
    }
    
    // Initialize Canvas Image Previews
    document.querySelectorAll('.folder-image-canvas').forEach(canvas => {
        const ctx = canvas.getContext('2d');
        const imageSrc = canvas.dataset.imageSrc;
        const fileName = canvas.dataset.fileName;
        
        // Set canvas size
        const rect = canvas.getBoundingClientRect();
        if (rect.width > 0 && rect.height > 0) {
            canvas.width = rect.width * window.devicePixelRatio;
            canvas.height = rect.height * window.devicePixelRatio;
            ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
        }
        
        // Show loading state
        ctx.fillStyle = '#f3f4f6';
        ctx.fillRect(0, 0, rect.width || 200, rect.height || 120);
        
        // Load image
        const img = new Image();
        img.crossOrigin = 'anonymous';
        
        img.onload = () => {
            const imgRatio = img.width / img.height;
            const canvasRatio = (rect.width || 200) / (rect.height || 120);
            
            let drawWidth, drawHeight, drawX, drawY;
            
            if (imgRatio > canvasRatio) {
                drawWidth = rect.width || 200;
                drawHeight = (rect.width || 200) / imgRatio;
                drawX = 0;
                drawY = ((rect.height || 120) - drawHeight) / 2;
            } else {
                drawHeight = rect.height || 120;
                drawWidth = (rect.height || 120) * imgRatio;
                drawX = ((rect.width || 200) - drawWidth) / 2;
                drawY = 0;
            }
            
            ctx.clearRect(0, 0, rect.width || 200, rect.height || 120);
            ctx.fillStyle = '#f9fafb';
            ctx.fillRect(0, 0, rect.width || 200, rect.height || 120);
            ctx.drawImage(img, drawX, drawY, drawWidth, drawHeight);
        };
        
        img.onerror = () => {
            // Draw error state
            ctx.fillStyle = '#fef2f2';
            ctx.fillRect(0, 0, rect.width || 200, rect.height || 120);
            ctx.fillStyle = '#ef4444';
            ctx.font = '20px Arial';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText('⚠', (rect.width || 200) / 2, (rect.height || 120) / 2 - 5);
        };
        
        img.src = imageSrc;
    });
    
    // Initialize Video Canvas Previews
    document.querySelectorAll('.folder-video-canvas').forEach(canvas => {
        const ctx = canvas.getContext('2d');
        const videoSrc = canvas.dataset.videoSrc;
        
        // Set canvas size
        const rect = canvas.getBoundingClientRect();
        if (rect.width > 0 && rect.height > 0) {
            canvas.width = rect.width * window.devicePixelRatio;
            canvas.height = rect.height * window.devicePixelRatio;
            ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
        }
        
        // Show loading state
        ctx.fillStyle = '#e5e7eb';
        ctx.fillRect(0, 0, rect.width || 200, rect.height || 120);
        
        // Create video element to capture frame
        const video = document.createElement('video');
        video.crossOrigin = 'anonymous';
        video.preload = 'metadata';
        video.muted = true;
        
        video.onloadedmetadata = function() {
            video.currentTime = 0.5; // Seek to 0.5 seconds
            
            // Update video metadata in the card
            const duration = Math.floor(video.duration);
            const mins = Math.floor(duration / 60);
            const secs = duration % 60;
            const durationText = `${mins}:${secs.toString().padStart(2, '0')}`;
            const resolutionText = `${video.videoWidth}x${video.videoHeight}`;
            
            // Find the metadata display for this video
            const metadataDiv = document.querySelector(`.video-metadata[data-video-src="${videoSrc}"]`);
            if (metadataDiv) {
                const durationEl = metadataDiv.querySelector('.duration-value');
                const resolutionEl = metadataDiv.querySelector('.resolution-value');
                if (durationEl) durationEl.textContent = durationText;
                if (resolutionEl) resolutionEl.textContent = resolutionText;
            }
        };
        
        video.onseeked = function() {
            // Calculate aspect ratio for cover fit
            const videoRatio = video.videoWidth / video.videoHeight;
            const canvasRatio = (rect.width || 200) / (rect.height || 120);
            
            let drawWidth, drawHeight, drawX, drawY;
            
            if (videoRatio > canvasRatio) {
                drawWidth = rect.width || 200;
                drawHeight = (rect.width || 200) / videoRatio;
                drawX = 0;
                drawY = ((rect.height || 120) - drawHeight) / 2;
            } else {
                drawHeight = rect.height || 120;
                drawWidth = (rect.height || 120) * videoRatio;
                drawX = ((rect.width || 200) - drawWidth) / 2;
                drawY = 0;
            }
            
            ctx.clearRect(0, 0, rect.width || 200, rect.height || 120);
            ctx.fillStyle = '#f9fafb';
            ctx.fillRect(0, 0, rect.width || 200, rect.height || 120);
            ctx.drawImage(video, drawX, drawY, drawWidth, drawHeight);
        };
        
        video.onerror = function() {
            // Draw error state with video icon
            ctx.fillStyle = '#dbeafe';
            ctx.fillRect(0, 0, rect.width || 200, rect.height || 120);
            ctx.fillStyle = '#3b82f6';
            ctx.font = '40px Arial';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText('▶', (rect.width || 200) / 2, (rect.height || 120) / 2);
        };
        
        video.src = videoSrc;
    });
    
    // Initialize PDF Canvas Previews
    document.querySelectorAll('.folder-pdf-canvas').forEach(canvas => {
        const ctx = canvas.getContext('2d');
        const pdfSrc = canvas.dataset.pdfSrc;
        const pdfName = canvas.dataset.pdfName;
        
        // Set canvas size
        const rect = canvas.getBoundingClientRect();
        if (rect.width > 0 && rect.height > 0) {
            canvas.width = rect.width * window.devicePixelRatio;
            canvas.height = rect.height * window.devicePixelRatio;
            ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
        }
        
        const width = rect.width || 200;
        const height = rect.height || 120;
        
        // Show loading state with white background
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, width, height);
        ctx.fillStyle = '#ef4444';
        ctx.font = '12px Arial';
        ctx.textAlign = 'center';
        ctx.fillText('Loading PDF...', width / 2, height / 2);
        
        // Load and render PDF
        pdfjsLib.getDocument(pdfSrc).promise.then(pdf => {
            return pdf.getPage(1);
        }).then(page => {
            const viewport = page.getViewport({ scale: 1 });
            const scale = Math.min(width / viewport.width, height / viewport.height);
            const scaledViewport = page.getViewport({ scale: scale });
            
            // Calculate center offsets
            const offsetX = (width - scaledViewport.width) / 2;
            const offsetY = (height - scaledViewport.height) / 2;
            
            // Clear loading text and set white background to match PDF
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, width, height);
            
            // Save context and translate to center
            ctx.save();
            ctx.translate(offsetX, offsetY);
            
            const renderContext = {
                canvasContext: ctx,
                viewport: scaledViewport
            };
            
            return page.render(renderContext).promise.then(() => {
                ctx.restore();
            });
        }).catch(error => {
            // Fallback to icon if PDF fails to load
            ctx.fillStyle = '#fef2f2';
            ctx.fillRect(0, 0, width, height);
            ctx.fillStyle = '#ef4444';
            ctx.font = 'bold 50px Arial';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText('📄', width / 2, height / 2 - 10);
        });
    });
    
    // Initialize DOC Canvas Previews
    document.querySelectorAll('.folder-doc-canvas').forEach(canvas => {
        const ctx = canvas.getContext('2d');
        const docName = canvas.dataset.docName;
        
        // Set canvas size
        const rect = canvas.getBoundingClientRect();
        if (rect.width > 0 && rect.height > 0) {
            canvas.width = rect.width * window.devicePixelRatio;
            canvas.height = rect.height * window.devicePixelRatio;
            ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
        }
        
        // Draw DOC background
        const width = rect.width || 200;
        const height = rect.height || 120;
        
        // White background like PDF
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, width, height);
        
        // Draw document page graphic
        const pageWidth = 60;
        const pageHeight = 80;
        const pageX = (width - pageWidth) / 2;
        const pageY = (height - pageHeight) / 2 - 15;
        
        // Page shadow
        ctx.fillStyle = 'rgba(0,0,0,0.1)';
        ctx.fillRect(pageX + 3, pageY + 3, pageWidth, pageHeight);
        
        // Page background
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(pageX, pageY, pageWidth, pageHeight);
        ctx.strokeStyle = '#3b82f6';
        ctx.lineWidth = 2;
        ctx.strokeRect(pageX, pageY, pageWidth, pageHeight);
        
        // Page corner fold
        ctx.beginPath();
        ctx.moveTo(pageX + pageWidth - 15, pageY);
        ctx.lineTo(pageX + pageWidth, pageY + 15);
        ctx.lineTo(pageX + pageWidth, pageY);
        ctx.closePath();
        ctx.fillStyle = '#dbeafe';
        ctx.fill();
        ctx.stroke();
        
        // Document lines (representing text)
        ctx.fillStyle = '#94a3b8';
        for (let i = 0; i < 5; i++) {
            ctx.fillRect(pageX + 8, pageY + 25 + (i * 10), pageWidth - 16, 4);
        }
        
        // Blue header bar
        ctx.fillStyle = '#3b82f6';
        ctx.fillRect(pageX + 8, pageY + 10, pageWidth - 16, 8);
        
        // Draw filename below page
        ctx.fillStyle = '#1e40af';
        ctx.font = '11px Arial';
        ctx.textAlign = 'center';
        const shortName = docName.length > 20 ? docName.substring(0, 17) + '...' : docName;
        ctx.fillText(shortName, width / 2, height / 2 + 50);
        
        // DOC badge removed from canvas - shown as overlay in HTML
    });
    
    // Initialize Excel/Spreadsheet Canvas Previews
    document.querySelectorAll('.folder-excel-canvas').forEach(canvas => {
        const ctx = canvas.getContext('2d');
        const docName = canvas.dataset.docName;
        const fileType = canvas.dataset.fileType;
        
        // Set canvas size
        const rect = canvas.getBoundingClientRect();
        if (rect.width > 0 && rect.height > 0) {
            canvas.width = rect.width * window.devicePixelRatio;
            canvas.height = rect.height * window.devicePixelRatio;
            ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
        }
        
        const width = rect.width || 200;
        const height = rect.height || 120;
        
        // White background
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, width, height);
        
        // Draw spreadsheet grid
        const gridWidth = 70;
        const gridHeight = 50;
        const gridX = (width - gridWidth) / 2;
        const gridY = (height - gridHeight) / 2 - 15;
        
        // Grid shadow
        ctx.fillStyle = 'rgba(0,0,0,0.1)';
        ctx.fillRect(gridX + 3, gridY + 3, gridWidth, gridHeight);
        
        // Grid background - green tint for Excel
        ctx.fillStyle = '#f0fdf4';
        ctx.fillRect(gridX, gridY, gridWidth, gridHeight);
        ctx.strokeStyle = '#22c55e';
        ctx.lineWidth = 2;
        ctx.strokeRect(gridX, gridY, gridWidth, gridHeight);
        
        // Draw grid lines
        ctx.strokeStyle = '#bbf7d0';
        ctx.lineWidth = 1;
        
        // Horizontal lines
        for (let i = 1; i < 4; i++) {
            ctx.beginPath();
            ctx.moveTo(gridX, gridY + (i * 12.5));
            ctx.lineTo(gridX + gridWidth, gridY + (i * 12.5));
            ctx.stroke();
        }
        
        // Vertical lines
        for (let i = 1; i < 4; i++) {
            ctx.beginPath();
            ctx.moveTo(gridX + (i * 17.5), gridY);
            ctx.lineTo(gridX + (i * 17.5), gridY + gridHeight);
            ctx.stroke();
        }
        
        // Header row (green)
        ctx.fillStyle = '#22c55e';
        ctx.fillRect(gridX + 1, gridY + 1, gridWidth - 2, 11);
        
        // Draw filename below grid
        ctx.fillStyle = '#166534';
        ctx.font = '11px Arial';
        ctx.textAlign = 'center';
        const shortName = docName.length > 20 ? docName.substring(0, 17) + '...' : docName;
        ctx.fillText(shortName, width / 2, height / 2 + 50);
    });
    
    // Initialize Text/Markdown Canvas Previews
    document.querySelectorAll('.folder-text-canvas').forEach(canvas => {
        const ctx = canvas.getContext('2d');
        const docName = canvas.dataset.docName;
        const fileType = canvas.dataset.fileType;
        
        // Set canvas size
        const rect = canvas.getBoundingClientRect();
        if (rect.width > 0 && rect.height > 0) {
            canvas.width = rect.width * window.devicePixelRatio;
            canvas.height = rect.height * window.devicePixelRatio;
            ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
        }
        
        const width = rect.width || 200;
        const height = rect.height || 120;
        
        // White background
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, width, height);
        
        // Draw text file page
        const pageWidth = 60;
        const pageHeight = 80;
        const pageX = (width - pageWidth) / 2;
        const pageY = (height - pageHeight) / 2 - 15;
        
        // Page shadow
        ctx.fillStyle = 'rgba(0,0,0,0.1)';
        ctx.fillRect(pageX + 3, pageY + 3, pageWidth, pageHeight);
        
        // Page background
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(pageX, pageY, pageWidth, pageHeight);
        ctx.strokeStyle = '#6b7280';
        ctx.lineWidth = 2;
        ctx.strokeRect(pageX, pageY, pageWidth, pageHeight);
        
        // Text lines (representing text content)
        ctx.fillStyle = '#374151';
        for (let i = 0; i < 6; i++) {
            const lineWidth = pageWidth - 16 - (i % 2) * 10;
            ctx.fillRect(pageX + 8, pageY + 15 + (i * 10), lineWidth, 3);
        }
        
        // File type icon
        ctx.fillStyle = '#6b7280';
        ctx.font = 'bold 14px Arial';
        ctx.textAlign = 'center';
        ctx.fillText(fileType.toUpperCase(), pageX + pageWidth / 2, pageY - 5);
        
        // Draw filename below page
        ctx.fillStyle = '#4b5563';
        ctx.font = '11px Arial';
        ctx.textAlign = 'center';
        const shortName = docName.length > 20 ? docName.substring(0, 17) + '...' : docName;
        ctx.fillText(shortName, width / 2, height / 2 + 50);
    });
    
    // Initialize Image Canvas Click Handlers for Cropping
    document.querySelectorAll('.folder-image-canvas').forEach(canvas => {
        canvas.style.cursor = 'pointer';
        canvas.title = 'Click to crop image';
        canvas.addEventListener('click', function() {
            const imageSrc = this.dataset.imageSrc;
            const fileName = this.dataset.fileName;
            openCropModal(imageSrc, fileName);
        });
    });
});

// Global cropper instance
let cropper = null;
let currentFileName = '';

// Open Image Viewer (legacy function for backward compatibility)
function openImageViewer(imageSrc) {
    window.dispatchEvent(new CustomEvent('open-file-viewer', { detail: { src: imageSrc, type: 'image' } }));
}

// Close Image Viewer (legacy function for backward compatibility)
function closeImageViewer() {
    // Handled by Alpine.js component
}

// Open Crop Modal
function openCropModal(imageSrc, fileName) {
    currentFileName = fileName || 'cropped-image.jpg';
    const modal = document.getElementById('cropModal');
    const img = document.getElementById('cropperImage');
    
    // Set image source
    img.src = imageSrc;
    img.crossOrigin = 'anonymous';
    
    // Show modal
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Initialize cropper once image loads
    img.onload = function() {
        if (cropper) {
            cropper.destroy();
        }
        
        cropper = new Cropper(img, {
            viewMode: 1,
            dragMode: 'crop',
            autoCropArea: 0.8,
            restore: false,
            guides: false,
            center: true,
            highlight: false,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
            responsive: true,
            checkCrossOrigin: true,
            checkOrientation: false,
            background: false,
            modal: false,
            highlightBox: false
        });
    };
}

// Close Crop Modal
function closeCropModal() {
    const modal = document.getElementById('cropModal');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
    
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
}

// Set Aspect Ratio
function setAspectRatio(ratio) {
    if (cropper) {
        cropper.setAspectRatio(ratio);
    }
    
    // Update button styles
    document.querySelectorAll('.aspect-btn').forEach(btn => {
        btn.classList.remove('bg-blue-600', 'text-white', 'hover:bg-blue-700');
        btn.classList.add('bg-gray-200', 'text-gray-700', 'hover:bg-gray-300');
    });
    
    const activeBtn = document.querySelector(`[data-ratio="${ratio === NaN ? 'free' : ratio}"]`) || 
                      document.querySelector('[data-ratio="free"]');
    if (activeBtn) {
        activeBtn.classList.remove('bg-gray-200', 'text-gray-700', 'hover:bg-gray-300');
        activeBtn.classList.add('bg-blue-600', 'text-white', 'hover:bg-blue-700');
    }
}

// Download Cropped Image
function downloadCroppedImage() {
    if (!cropper) return;
    
    // Get cropped canvas
    const canvas = cropper.getCroppedCanvas({
        fillColor: '#fff',
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high'
    });
    
    if (canvas) {
        // Create download link
        const link = document.createElement('a');
        link.download = 'cropped-' + currentFileName;
        link.href = canvas.toDataURL('image/jpeg', 0.9);
        link.click();
    }
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('cropModal').classList.contains('hidden')) {
        closeCropModal();
    }
});

// Drag and Drop File Upload
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const uploadForm = document.getElementById('uploadForm');
    
    if (!dropZone || !fileInput || !uploadForm) return;

    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });

    // Highlight drop zone
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });

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
        if (files.length > 0) {
            fileInput.files = files;
            uploadForm.submit();
        }
    }

    // Click on dropZone (but not on button) triggers file input
    dropZone.addEventListener('click', function(e) {
        if (e.target.tagName !== 'BUTTON' && !e.target.closest('button')) {
            fileInput.click();
        }
    });
});

// Move Asset Modal Functions
let folderCache = {};
let currentMoveAssetId = null;
let allFoldersData = null;
let currentFolderIdForModal = null;

function openMoveAssetModal(assetId, assetName, projectId, currentFolderId) {
    currentMoveAssetId = assetId;
    currentFolderIdForModal = currentFolderId;
    const modal = document.getElementById('moveAssetModal');
    const fileNameEl = document.getElementById('moveAssetFileName');
    const foldersContainer = document.getElementById('moveAssetFolders');
    const searchInput = document.getElementById('folderSearchInput');
    
    fileNameEl.textContent = assetName;
    searchInput.value = '';
    
    // Show modal
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Load folders
    if (!folderCache[projectId]) {
        loadFoldersForModal(projectId, currentFolderId, foldersContainer);
    } else {
        allFoldersData = folderCache[projectId];
        renderFoldersForModal(allFoldersData, currentFolderId, foldersContainer);
    }
}

function closeMoveAssetModal() {
    const modal = document.getElementById('moveAssetModal');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
    currentMoveAssetId = null;
    currentFolderIdForModal = null;
    allFoldersData = null;
}

async function loadFoldersForModal(projectId, currentFolderId, container) {
    container.innerHTML = '<div class="px-4 py-8 text-center text-xs text-gray-400"><svg class="animate-spin h-5 w-5 mx-auto mb-2 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><p class="font-medium">Loading folders...</p></div>';
    
    try {
        const response = await fetch(`/projects/${projectId}/folder-tree`);
        if (!response.ok) throw new Error('Failed to load folders');
        
        const folders = await response.json();
        folderCache[projectId] = folders;
        allFoldersData = folders;
        
        renderFoldersForModal(folders, currentFolderId, container);
    } catch (error) {
        container.innerHTML = '<div class="px-4 py-8 text-xs text-red-500 text-center"><svg class="w-8 h-8 mx-auto mb-2 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><p class="font-medium">Error loading folders</p></div>';
        console.error('Error loading folders:', error);
    }
}

function renderFoldersForModal(folders, currentFolderId, container, parentPath = '') {
    if (!folders || folders.length === 0) {
        container.innerHTML = '<div class="px-4 py-8 text-xs text-gray-400 text-center"><svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg><p class="font-medium">No folders available</p></div>';
        return;
    }
    
    let html = '';
    
    folders.forEach(folder => {
        const fullPath = parentPath ? `${parentPath} / ${folder.name}` : folder.name;
        html += renderFolderItemForModal(folder, currentFolderId, 0, fullPath);
    });
    
    container.innerHTML = html;
}

function renderFolderItemForModal(folder, currentFolderId, level, fullPath = '') {
    const isCurrent = folder.id === currentFolderId;
    const indent = level * 12;
    const disabledClass = isCurrent ? 'opacity-50 cursor-not-allowed bg-gray-100' : 'hover:bg-indigo-50 hover:text-indigo-700 cursor-pointer group';
    const folderName = folder.name.replace(/'/g, "\\'");
    const pathDisplay = fullPath.replace(/'/g, "\\'");
    
    let html = `
        <button type="button"
            onclick="${isCurrent ? '' : `moveAssetToFolder(${folder.id}, '${folderName}', '${pathDisplay}')`}"
            class="w-full text-left px-3 py-2 text-xs text-gray-700 transition-all flex items-center border-b border-gray-200 last:border-b-0 ${disabledClass}"
            style="padding-left: ${16 + indent}px"
            ${isCurrent ? 'disabled' : ''}
            title="Directory: ${pathDisplay}">
            <svg class="w-4 h-4 mr-2 text-amber-500 flex-shrink-0 ${isCurrent ? '' : 'group-hover:text-amber-600'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
            <div class="flex flex-col flex-1 min-w-0">
                <span class="font-medium truncate">${folder.name} ${isCurrent ? '<span class="text-xs text-gray-400 ml-1">(current)</span>' : ''}</span>
                ${!isCurrent ? `<span class="text-xs text-gray-400 truncate group-hover:text-indigo-400">${fullPath}</span>` : ''}
            </div>
            ${!isCurrent ? `
                <svg class="w-3.5 h-3.5 text-gray-300 group-hover:text-indigo-500 ml-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            ` : ''}
        </button>
    `;
    
    if (folder.children && folder.children.length > 0) {
        folder.children.forEach(child => {
            const childPath = fullPath ? `${fullPath} / ${child.name}` : child.name;
            html += renderFolderItemForModal(child, currentFolderId, level + 1, childPath);
        });
    }
    
    return html;
}

function filterFolders(searchTerm) {
    if (!allFoldersData) return;
    
    const container = document.getElementById('moveAssetFolders');
    
    if (!searchTerm || searchTerm.trim() === '') {
        renderFoldersForModal(allFoldersData, currentFolderIdForModal, container);
        return;
    }
    
    const filteredFolders = filterFoldersRecursive(allFoldersData, searchTerm.toLowerCase());
    
    if (filteredFolders.length === 0) {
        container.innerHTML = '<div class="px-4 py-8 text-xs text-gray-400 text-center"><svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg><p class="font-medium">No folders found</p><p class="text-xs mt-1">Try a different search term</p></div>';
        return;
    }
    
    renderFoldersForModal(filteredFolders, currentFolderIdForModal, container);
}

function filterFoldersRecursive(folders, searchTerm) {
    const filtered = [];
    
    folders.forEach(folder => {
        const matches = folder.name.toLowerCase().includes(searchTerm);
        const filteredChildren = folder.children && folder.children.length > 0 
            ? filterFoldersRecursive(folder.children, searchTerm) 
            : [];
        
        if (matches || filteredChildren.length > 0) {
            filtered.push({
                ...folder,
                children: filteredChildren.length > 0 ? filteredChildren : folder.children
            });
        }
    });
    
    return filtered;
}

async function moveAssetToFolder(targetFolderId, folderName, directoryPath = '') {
    const displayLocation = directoryPath || folderName;
    
    try {
        const formData = new FormData();
        formData.append('asset_id', currentMoveAssetId);
        formData.append('target_folder_id', targetFolderId || '');
        formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');

        const response = await fetch('/assets/move', {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            const result = await response.json();
            closeMoveAssetModal();
            showToast(`Moved to: ${displayLocation}`, 'success');
            
            // Refresh the page after successful move
            setTimeout(() => {
                location.reload();
            }, 500);
        } else {
            const error = await response.json();
            showToast(error.error || error.message || 'Failed to move file', 'error');
        }
    } catch (error) {
        console.error('Error moving asset:', error);
        showToast('Failed to move file', 'error');
    }
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 text-white font-medium transform transition-all duration-300 translate-y-full opacity-0 ${
        type === 'success' ? 'bg-green-600' : 'bg-red-600'
    }`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.remove('translate-y-full', 'opacity-0');
    }, 10);
    
    setTimeout(() => {
        toast.classList.add('translate-y-full', 'opacity-0');
        setTimeout(() => {
            if (toast.parentNode) {
                document.body.removeChild(toast);
            }
        }, 300);
    }, 3000);
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('moveAssetModal').classList.contains('hidden')) {
        closeMoveAssetModal();
    }
});
</script>
