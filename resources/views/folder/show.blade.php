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
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex space-x-2">
                            <!-- Create Subfolder Button -->
                            <a href="{{ route('folders.show', $folder->id) }}?create_subfolder=1" 
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
                    </div>

                    <!-- Success Message -->
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Create Subfolder Form -->
                    @if(request('create_subfolder'))
                        <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <form action="{{ route('folders.store') }}" method="POST">
                                @csrf
                                <div class="flex items-center space-x-4">
                                    <input type="text" name="name" placeholder="Subfolder name" required
                                           class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-600 dark:text-white">
                                    <input type="hidden" name="parent_folder" value="{{ $folder->id }}">
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
                    <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 hover:border-blue-500 dark:hover:border-blue-400 transition-colors mb-6">
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
                                                class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium flex items-center justify-center">
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

                    <!-- Folder Contents -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h2 class="text-xl font-semibold mb-4 text-gray-800">Folder Contents</h2>
                        
                        @if($folder->children->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($folder->children as $child)
                                    <div class="bg-white border rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer">
                                        <a href="{{ route('folders.show', $child->id) }}" class="block">
                                            <div class="flex items-center mb-2">
                                                <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                                </svg>
                                                <span class="font-medium text-gray-800">{{ $child->name }}</span>
                                            </div>
                                            <p class="text-sm text-gray-600">
                                                {{ $child->children->count() }} subfolder(s)
                                            </p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                Created {{ $child->created_at->format('M d, Y') }}
                                            </p>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
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
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                                @foreach($folder->assets as $asset)
                                    <div class="bg-white border rounded-lg p-3 hover:shadow-md transition-shadow">
                                        <div class="flex items-center mb-2">
                                            @if($asset->file_type === 'image')
                                                <svg class="w-5 h-5 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                                                </svg>
                                            @elseif($asset->file_type === 'video')
                                                <svg class="w-5 h-5 mr-2 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm3 2h6v4l-2-1.5L9 9V5z" clip-rule="evenodd"/>
                                                </svg>
                                            @elseif($asset->file_type === 'pdf')
                                                <svg class="w-5 h-5 mr-2 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-5L9 2H4z" clip-rule="evenodd"/>
                                                </svg>
                                            @elseif($asset->file_type === 'doc')
                                                <svg class="w-5 h-5 mr-2 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-5L9 2H4z" clip-rule="evenodd"/>
                                                </svg>
                                            @else
                                                <svg class="w-5 h-5 mr-2 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-5L9 2H4z" clip-rule="evenodd"/>
                                                </svg>
                                            @endif
                                            <span class="text-sm font-medium text-gray-700 truncate">{{ $asset->name }}</span>
                                        </div>
                                        <p class="text-xs text-gray-500">{{ $asset->formatted_size }}</p>
                                        <div class="mt-2 flex justify-center space-x-2">
                                            @if($asset->file_type === 'image')
                                                <a href="{{ Storage::url($asset->file_path) }}" target="_blank" 
                                                   class="text-blue-600 hover:text-blue-800 text-xs">View</a>
                                            @endif
                                            <a href="{{ Storage::url($asset->file_path) }}" download 
                                               class="text-green-600 hover:text-green-800 text-xs">Download</a>
                                            
                                            <!-- Delete Asset Form -->
                                            <form action="{{ route('folders.file.delete') }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="path" value="{{ $asset->file_path }}">
                                                <button type="submit" 
                                                        class="text-red-600 hover:text-red-800 text-xs">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
            </div>

    <!-- Create Subfolder Modal -->
    <x-create-folder-modal :projectId="$folder->project->id" :parentFolderId="$folder->id" />

</x-app-layout>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('fileInput');
    const uploadForm = document.getElementById('uploadForm');

    // File input change handler - auto-upload when files are selected
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            uploadForm.submit();
        }
    });
});
</script>
