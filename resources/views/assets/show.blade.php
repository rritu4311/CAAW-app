<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Asset Details') }}
        </h2>
    </x-slot>

    <div class="py-12" id="asset-page">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl overflow-hidden">
                
                <!-- Breadcrumb Navigation -->
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                    <nav class="flex items-center space-x-2 text-sm">
                        @if($asset->folder && $asset->folder->project)
                            <a href="{{ route('projects.show', $asset->folder->project->id) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $asset->folder->project->name }}
                            </a>
                            <span class="text-gray-400">/</span>
                            
                            @if($asset->folder->parent)
                                <a href="{{ route('folders.show', $asset->folder->parent->id) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $asset->folder->parent->name }}
                                </a>
                                <span class="text-gray-400">/</span>
                            @endif
                            
                            <a href="{{ route('folders.show', $asset->folder->id) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $asset->folder->name }}
                            </a>
                            <span class="text-gray-400">/</span>
                        @endif
                        <span class="text-gray-700 font-medium">{{ $asset->name }}</span>
                    </nav>
                </div>

                <!-- Asset Preview Section -->
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                        <!-- Preview Area (Left Column) -->
                        <div class="lg:col-span-1">
                            <div class="relative bg-gray-50 dark:bg-gray-900 rounded-lg p-4 flex items-center justify-center" style="min-height: calc(100vh - 80px);">
                                <!-- Full-screen button -->
                                <button onclick="toggleFullscreen('asset-preview')"
                                        class="absolute top-2 right-2 z-10 p-2 bg-gray-800 hover:bg-gray-700 text-white rounded-lg shadow-lg transition-colors"
                                        title="Toggle Full Screen">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                                    </svg>
                                </button>

                                <div id="asset-preview" class="w-full h-full flex items-center justify-center">
                                    @if($asset->file_type === 'image')
                                        <img src="{{ Storage::url($asset->file_path) }}"
                                             alt="{{ $asset->name }}"
                                             class="max-w-full object-contain rounded-lg shadow-lg" style="max-height: calc(100vh - 100px);">
                                    @elseif($asset->file_type === 'video')
                                        <video controls class="max-w-full rounded-lg shadow-lg" style="max-height: calc(100vh - 100px);">
                                            <source src="{{ Storage::url($asset->file_path) }}" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                    @elseif($asset->file_type === 'pdf')
                                        <iframe src="{{ Storage::url($asset->file_path) }}"
                                                class="w-full rounded-lg border-0"
                                                style="height: calc(100vh - 80px);"
                                                type="application/pdf"></iframe>
                                @elseif(in_array($asset->file_type, ['doc', 'docx', 'xlsx', 'xls', 'csv']))
                                    <div class="text-center p-8">
                                        <div class="w-24 h-24 bg-gray-200 dark:bg-gray-700 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                            @if(in_array($asset->file_type, ['doc', 'docx']))
                                                <svg class="w-12 h-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            @else
                                                <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            @endif
                                        </div>
                                        <h3 class="text-xl font-semibold text-gray-800 dark:text-white mb-2">{{ $asset->name }}</h3>
                                        <p class="text-gray-500 dark:text-gray-400 mb-6">This file type cannot be previewed directly in the browser.</p>
                                        <a href="{{ Storage::url($asset->file_path) }}" download="{{ $asset->name }}"
                                           class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                            Download File
                                        </a>
                                    </div>
                                @elseif(in_array($asset->file_type, ['txt', 'md', 'markdown']))
                                    <iframe src="{{ Storage::url($asset->file_path) }}"
                                            class="w-full rounded-lg border-0 bg-white"
                                            style="height: calc(100vh - 80px);"></iframe>
                                @else
                                    <div class="text-center p-8">
                                        <div class="w-24 h-24 bg-gray-200 dark:bg-gray-700 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <h3 class="text-xl font-semibold text-gray-800 dark:text-white mb-2">{{ $asset->name }}</h3>
                                        <p class="text-gray-500 dark:text-gray-400 mb-6">Preview not available for this file type.</p>
                                        <a href="{{ Storage::url($asset->file_path) }}" download="{{ $asset->name }}"
                                           class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                            Download File
                                        </a>
                                    </div>
                                @endif
                                </div>
                            </div>
                        </div>

                        <!-- Asset Information (Middle Column) -->
                        <div class="lg:col-span-1">
                            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Asset Information</h3>
                                
                                <!-- Status Badge -->
                                <div class="mb-4">
                                    @if($asset->isDraft())
                                        <div class="flex items-center gap-2 px-3 py-2 bg-gray-200 dark:bg-gray-700 rounded-lg">
                                            <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Draft</span>
                                        </div>
                                    @elseif($asset->isInReview())
                                        <div class="flex items-center gap-2 px-3 py-2 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg">
                                            <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span class="text-sm font-medium text-yellow-800 dark:text-yellow-300">In Review</span>
                                        </div>
                                    @elseif($asset->isApproved())
                                        <div class="flex items-center gap-2 px-3 py-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                                            <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span class="text-sm font-medium text-green-800 dark:text-green-300">Approved</span>
                                        </div>
                                    @elseif($asset->isRejected())
                                        <div class="flex items-center gap-2 px-3 py-2 bg-red-100 dark:bg-red-900/30 rounded-lg">
                                            <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            <span class="text-sm font-medium text-red-800 dark:text-red-300">Rejected</span>
                                        </div>
                                    @elseif($asset->hasChangesRequested())
                                        <div class="flex items-center gap-2 px-3 py-2 bg-orange-100 dark:bg-orange-900/30 rounded-lg">
                                            <svg class="w-4 h-4 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            <span class="text-sm font-medium text-orange-800 dark:text-orange-300">Changes Requested</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="space-y-4">
                                    <!-- File Type with Icon -->
                                    <div class="flex items-center gap-3 p-3 bg-white dark:bg-gray-800 rounded-lg">
                                        <div class="w-12 h-12 rounded-lg bg-blue-100 dark:bg-blue-900 flex items-center justify-center flex-shrink-0">
                                            @if($asset->file_type === 'pdf')
                                                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                </svg>
                                            @elseif($asset->file_type === 'image')
                                                <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            @elseif($asset->file_type === 'video')
                                                <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                </svg>
                                            @elseif(in_array($asset->file_type, ['doc', 'docx']))
                                                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            @elseif(in_array($asset->file_type, ['xlsx', 'xls', 'csv']))
                                                <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            @else
                                                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                </svg>
                                            @endif
                                        </div>
                                        <div>
                                            <label class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">File Type</label>
                                            <p class="text-gray-800 dark:text-white text-sm font-medium uppercase">{{ $asset->file_type }}</p>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">File Name</label>
                                        <p class="text-gray-800 dark:text-white text-sm font-medium break-words">{{ $asset->name }}</p>
                                    </div>
                                    
                                    <div>
                                        <label class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">Version</label>
                                        <p class="text-gray-800 dark:text-white text-sm font-medium">v{{ number_format($asset->version, 1) }}</p>
                                    </div>
                                    
                                    <div>
                                        <label class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">File Size</label>
                                        <p class="text-gray-800 dark:text-white text-sm font-medium">{{ $asset->formatted_size }}</p>
                                    </div>
                                    
                                    <div>
                                        <label class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">Uploaded By</label>
                                        <p class="text-gray-800 dark:text-white text-sm font-medium">{{ $asset->uploadedBy->name ?? 'Unknown' }}</p>
                                    </div>
                                    
                                    <div>
                                        <label class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">Uploaded At</label>
                                        <p class="text-gray-800 dark:text-white text-sm font-medium">{{ $asset->created_at->format('M d, Y - g:i A') }}</p>
                                    </div>
                                    
                                    @if($asset->folder)
                                        <div>
                                            <label class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">Folder</label>
                                            <p class="text-gray-800 dark:text-white text-sm font-medium">
                                                <a href="{{ route('folders.show', $asset->folder->id) }}" class="text-blue-600 hover:text-blue-800">
                                                    {{ $asset->folder->name }}
                                                </a>
                                            </p>
                                        </div>
                                    @endif
                                    
                                    @if($asset->folder && $asset->folder->project)
                                        <div>
                                            <label class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider">Project</label>
                                            <p class="text-gray-800 dark:text-white text-sm font-medium">
                                                <a href="{{ route('projects.show', $asset->folder->project->id) }}" class="text-blue-600 hover:text-blue-800">
                                                    {{ $asset->folder->project->name }}
                                                </a>
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Right Sidebar (Annotations, Version History, Actions) -->
                        <div class="lg:col-span-1">
                            <div class="space-y-6">

                                <!-- Annotations Section (only for images and asset owner) -->
                                @if($asset->isImage() && $asset->annotations && $asset->annotations->count() > 0 && auth()->check() && auth()->id() === $asset->uploaded_by)
                                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Annotations</h3>
                                        <div class="space-y-4">
                                            @foreach($asset->annotations as $annotation)
                                                <div class="p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                                    <div class="flex items-start justify-between mb-3">
                                                        <div class="flex items-center gap-2">
                                                            @if($annotation->isPending())
                                                                <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300 rounded-full">Pending</span>
                                                            @elseif($annotation->isAcknowledged())
                                                                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 rounded-full">Acknowledged</span>
                                                            @elseif($annotation->isResolved())
                                                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 rounded-full">Resolved</span>
                                                            @endif
                                                            <button onclick="viewAnnotationOnImage({{ $annotation->x }}, {{ $annotation->y }}, {{ $annotation->width ?? 0 }}, {{ $annotation->height ?? 0 }})"
                                                                    class="text-xs px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded transition-colors">
                                                                View
                                                            </button>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            @if(auth()->check() && auth()->id() === $annotation->created_by)
                                                                <button onclick="deleteAnnotation({{ $annotation->id }})"
                                                                        class="text-xs px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded transition-colors">
                                                                    Delete
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <!-- Comments for this annotation -->
                                                    @if($annotation->comments && $annotation->comments->count() > 0)
                                                        <div class="space-y-2 mt-3">
                                                            @foreach($annotation->comments as $comment)
                                                                <div class="p-3 bg-gray-50 dark:bg-gray-900 rounded border border-gray-200 dark:border-gray-600">
                                                                    <div class="flex items-center gap-2 mb-1">
                                                                        <span class="text-xs font-medium text-gray-900 dark:text-white">{{ $comment->user->name ?? 'Unknown' }}</span>
                                                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $comment->created_at->format('M d, Y - g:i A') }}</span>
                                                                    </div>
                                                                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $comment->text }}</p>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- General Comments Section (not linked to annotations, only for asset owner) -->
                                @if($asset->comments && $asset->comments->whereNull('annotation_id')->count() > 0 && auth()->check() && auth()->id() === $asset->uploaded_by)
                                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Comments</h3>
                                        <div class="space-y-3">
                                            @foreach($asset->comments->whereNull('annotation_id') as $comment)
                                                <div class="p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                                    <div class="flex items-center gap-2 mb-2">
                                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $comment->user->name ?? 'Unknown' }}</span>
                                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $comment->created_at->format('M d, Y - g:i A') }}</span>
                                                    </div>
                                                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $comment->text }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Version History Section -->
                                @if($asset->versions && $asset->versions->count() > 0)
                                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6">
                                        <div class="flex items-center justify-between mb-4">
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Version History</h3>
                                            @if(auth()->check() && auth()->id() === $asset->uploaded_by)
                                                <button onclick="document.getElementById('uploadVersionModal').style.display='flex'"
                                                        class="text-sm px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded transition-colors">
                                                    Upload New Version
                                                </button>
                                            @endif
                                        </div>
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-sm">
                                                <thead>
                                                    <tr class="border-b border-gray-200 dark:border-gray-700">
                                                        <th class="text-left py-2 px-3 text-gray-600 dark:text-gray-400 font-medium">Version</th>
                                                        <th class="text-left py-2 px-3 text-gray-600 dark:text-gray-400 font-medium">File Size</th>
                                                        <th class="text-left py-2 px-3 text-gray-600 dark:text-gray-400 font-medium">Uploaded By</th>
                                                        <th class="text-left py-2 px-3 text-gray-600 dark:text-gray-400 font-medium">Date</th>
                                                        <th class="text-center py-2 px-3 text-gray-600 dark:text-gray-400 font-medium">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $versionsToShow = (auth()->check() && auth()->id() === $asset->uploaded_by)
                                                            ? $asset->versions->sortByDesc('version_number')
                                                            : $asset->versions->filter(function($v) use ($asset) { return $v->id === $asset->current_version_id; });
                                                    @endphp
                                                    @foreach($versionsToShow as $version)
                                                        <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800">
                                                            <td class="py-2 px-3">
                                                                <span class="font-medium {{ $version->id === $asset->current_version_id ? 'text-green-600 dark:text-green-400' : 'text-gray-700 dark:text-gray-300' }}">
                                                                    {{ $version->formatted_version }}
                                                                    @if($version->id === $asset->current_version_id)
                                                                        <span class="ml-1 text-xs">(Current)</span>
                                                                    @endif
                                                                </span>
                                                            </td>
                                                            <td class="py-2 px-3 text-gray-600 dark:text-gray-400">{{ $version->formatted_size }}</td>
                                                            <td class="py-2 px-3 text-gray-600 dark:text-gray-400">{{ $version->uploadedBy->name ?? 'Unknown' }}</td>
                                                            <td class="py-2 px-3 text-gray-600 dark:text-gray-400">{{ $version->created_at->format('M d, Y') }}</td>
                                                            <td class="py-2 px-3 text-center">
                                                                <a href="{{ route('assets.view-version', ['asset' => $asset->id, 'version' => $version->id]) }}"
                                                                   class="text-xs px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded transition-colors">
                                                                    View
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif

                                <!-- Actions -->
                                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Actions</h3>
                                    <div class="space-y-3">
                                        <!-- Approval Workflow Buttons -->
                                        @if($asset->isDraft())
                                            <form method="POST" action="{{ route('assets.submit-for-review', $asset->id) }}" class="w-full mb-3">
                                                @csrf
                                                <button type="submit" 
                                                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    Submit for Review
                                                </button>
                                            </form>
                                        @elseif($asset->isInReview())
                                            @auth
                                                @if(auth()->user()->can('approve', $asset) && auth()->id() !== $asset->uploaded_by)
                                                    <div class="space-y-2">
                                                        <form method="POST" action="{{ route('assets.approve', $asset->id) }}" class="w-full">
                                                            @csrf
                                                            <button type="submit"
                                                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium transition-colors">
                                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                                </svg>
                                                                Approve
                                                            </button>
                                                        </form>
                                                        <button onclick="document.getElementById('rejectModal').style.display='flex'"
                                                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                            Reject
                                                        </button>
                                                        <button onclick="openRequestChangesModal()"
                                                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-medium transition-colors">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                            </svg>
                                                            Request Changes

                                                        </button>
                                                        <br><br>
                                                    </div>
                                                @else
                                                    <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                                        <p class="text-sm text-yellow-800 dark:text-yellow-300">Awaiting approval decision</p>
                                                    </div>
                                                @endif
                                            @endauth
                                        @elseif($asset->isApproved())
                                            <div class="p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    <p class="text-sm font-medium text-green-800 dark:text-green-300">Approved</p>
                                                </div>
                                            </div>
                                        @elseif($asset->isRejected())
                                            <div class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                    <p class="text-sm font-medium text-red-800 dark:text-red-300">Rejected</p>
                                                </div>
                                                @if($asset->currentVersion && $asset->currentVersion->notes)
                                                    <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $asset->currentVersion->notes }}</p>
                                                @endif
                                            </div>
                                        @elseif($asset->hasChangesRequested())
                                            <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                    <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300">Changes Requested</p>
                                                </div>
                                                @if($asset->currentVersion && $asset->currentVersion->notes)
                                                    <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">{{ $asset->currentVersion->notes }}</p>
                                                @endif
                                            </div>
                                        @endif

                                        <!-- Download Button -->
                                        <a href="{{ Storage::url($asset->file_path) }}" download="{{ $asset->name }}" 
                                           class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                            Download
                                        </a>
                                        
                                        @if($asset->folder)
                                            <a href="{{ route('folders.show', $asset->folder->id) }}" 
                                               class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-white rounded-lg font-medium transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                                </svg>
                                                Back to Folder
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<!-- Edit Folder Modal -->
<x-edit-folder-modal />

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Reject Asset</h3>
            <form method="POST" action="{{ route('assets.reject', $asset->id) }}">
                @csrf
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="document.getElementById('rejectModal').style.display='none'"
                            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-white rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Request Changes Modal -->
<div id="requestChangesModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50 p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-5xl max-h-[95vh] flex flex-col">
        <div class="p-6 flex-shrink-0 overflow-y-auto" style="max-height: calc(95vh - 2rem);">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Request Changes</h3>
            <form method="POST" action="{{ route('assets.request-changes', $asset->id) }}" id="requestChangesForm">
                @csrf
                <input type="hidden" name="annotation_id" id="requestChangesAnnotationId" value="">

                <!-- Annotation Section (only for images) -->
                @if($asset->isImage())
                    <div class="mb-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">Add Annotation (Optional)</h4>
                            <button type="button" onclick="toggleAnnotationFields()" class="text-xs text-blue-600 hover:text-blue-800">
                                + Add Annotation
                            </button>
                        </div>
                        <div id="annotationFields" style="display: none;">
                            <!-- Image Preview for Selection -->
                            <div class="mb-4">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Select area on image to mark for changes</label>
                                <div class="relative border border-gray-300 dark:border-gray-600 rounded overflow-hidden cursor-crosshair" id="imageSelectionContainer">
                                    <img src="{{ Storage::url($asset->file_path) }}" alt="{{ $asset->name }}" id="annotationImage" class="max-w-full h-auto" style="max-height: 400px;" draggable="false">
                                    <div id="selectionBox" class="absolute border-2 border-red-500 bg-red-500 bg-opacity-20 pointer-events-none" style="display: none;"></div>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Click and drag on the image to select an area</p>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">X Position</label>
                                    <input type="number" step="0.01" name="annotation_x" id="annotationX" readonly
                                           class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-gray-100 dark:bg-gray-600 dark:text-gray-300"
                                           placeholder="X coordinate">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Y Position</label>
                                    <input type="number" step="0.01" name="annotation_y" id="annotationY" readonly
                                           class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-gray-100 dark:bg-gray-600 dark:text-gray-300"
                                           placeholder="Y coordinate">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Width</label>
                                    <input type="number" step="0.01" name="annotation_width" id="annotationWidth" readonly
                                           class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-gray-100 dark:bg-gray-600 dark:text-gray-300"
                                           placeholder="Width (optional)">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Height</label>
                                    <input type="number" step="0.01" name="annotation_height" id="annotationHeight" readonly
                                           class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded bg-gray-100 dark:bg-gray-600 dark:text-gray-300"
                                           placeholder="Height (optional)">
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Comments (Required)</label>
                    <textarea name="comments" rows="4" required
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                              placeholder="Please describe the changes needed..."></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="document.getElementById('requestChangesModal').style.display='none'"
                            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-white rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 btn btn-warning">
                        Request Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Annotation Viewer Modal -->
<div id="annotationViewerModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50 p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-5xl max-h-[95vh] flex flex-col">
        <div class="p-6 flex-shrink-0">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Annotation View</h3>
                <button type="button" onclick="document.getElementById('annotationViewerModal').style.display='none'"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="relative border border-gray-300 dark:border-gray-600 rounded overflow-hidden" id="viewerImageContainer" style="max-height: 500px; overflow: auto;">
                <img src="{{ Storage::url($asset->file_path) }}" alt="{{ $asset->name }}" id="viewerImage" class="max-w-full h-auto" style="max-height: 500px;">
                <div id="viewerAnnotationBox" class="absolute border-4 border-red-500 bg-red-500 bg-opacity-30 pointer-events-none" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Upload New Version Modal -->
<div id="uploadVersionModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50 p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Upload New Version</h3>
                <button type="button" onclick="document.getElementById('uploadVersionModal').style.display='none'"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form method="POST" action="{{ route('assets.upload-version', $asset->id) }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Select File
                    </label>
                    <input type="file" name="file" required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                           accept="{{ $asset->file_type === 'image' ? 'image/*' : ($asset->file_type === 'video' ? 'video/*' : ($asset->file_type === 'pdf' ? '.pdf' : '.doc,.docx,.xlsx,.xls')) }}">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        File type must match the original asset ({{ $asset->file_type }})
                    </p>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="document.getElementById('uploadVersionModal').style.display='none'"
                            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-white rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Upload Version
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleFullscreen(elementId) {
    const element = document.getElementById(elementId);
    if (!document.fullscreenElement) {
        if (element.requestFullscreen) {
            element.requestFullscreen();
        } else if (element.webkitRequestFullscreen) { /* Safari */
            element.webkitRequestFullscreen();
        } else if (element.msRequestFullscreen) { /* IE11 */
            element.msRequestFullscreen();
        }
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.webkitExitFullscreen) { /* Safari */
            document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) { /* IE11 */
            document.msExitFullscreen();
        }
    }
}

// Open requestChanges modal with optional annotation_id
function openRequestChangesModal(annotationId = null) {
    document.getElementById('requestChangesAnnotationId').value = annotationId || '';
    document.getElementById('requestChangesModal').style.display = 'flex';
    // Reset annotation fields
    const annotationFields = document.getElementById('annotationFields');
    if (annotationFields) {
        annotationFields.style.display = 'none';
    }
    const annotationX = document.getElementById('annotationX');
    const annotationY = document.getElementById('annotationY');
    const annotationWidth = document.getElementById('annotationWidth');
    const annotationHeight = document.getElementById('annotationHeight');
    if (annotationX) annotationX.value = '';
    if (annotationY) annotationY.value = '';
    if (annotationWidth) annotationWidth.value = '';
    if (annotationHeight) annotationHeight.value = '';
    // Reset selection
    const selectionBox = document.getElementById('selectionBox');
    if (selectionBox) {
        selectionBox.style.display = 'none';
        selectionBox.style.left = '0';
        selectionBox.style.top = '0';
        selectionBox.style.width = '0';
        selectionBox.style.height = '0';
    }
}

// Toggle annotation fields visibility
function toggleAnnotationFields() {
    const annotationFields = document.getElementById('annotationFields');
    if (annotationFields) {
        if (annotationFields.style.display === 'none') {
            annotationFields.style.display = 'block';
            // Initialize image selection functionality
            initImageSelection();
        } else {
            annotationFields.style.display = 'none';
        }
    }
}

// Initialize image selection functionality
function initImageSelection() {
    const container = document.getElementById('imageSelectionContainer');
    const image = document.getElementById('annotationImage');
    const selectionBox = document.getElementById('selectionBox');
    
    if (!container || !image || !selectionBox) return;

    let isSelecting = false;
    let startX, startY;

    container.addEventListener('mousedown', function(e) {
        if (e.target !== image) return;
        isSelecting = true;
        const rect = container.getBoundingClientRect();
        startX = e.clientX - rect.left;
        startY = e.clientY - rect.top;
        
        selectionBox.style.display = 'block';
        selectionBox.style.left = startX + 'px';
        selectionBox.style.top = startY + 'px';
        selectionBox.style.width = '0';
        selectionBox.style.height = '0';
    });

    container.addEventListener('mousemove', function(e) {
        if (!isSelecting) return;
        const rect = container.getBoundingClientRect();
        const currentX = e.clientX - rect.left;
        const currentY = e.clientY - rect.top;
        
        const width = Math.abs(currentX - startX);
        const height = Math.abs(currentY - startY);
        const left = Math.min(startX, currentX);
        const top = Math.min(startY, currentY);
        
        selectionBox.style.left = left + 'px';
        selectionBox.style.top = top + 'px';
        selectionBox.style.width = width + 'px';
        selectionBox.style.height = height + 'px';
    });

    container.addEventListener('mouseup', function(e) {
        if (!isSelecting) return;
        isSelecting = false;
        
        const rect = container.getBoundingClientRect();
        const currentX = e.clientX - rect.left;
        const currentY = e.clientY - rect.top;
        
        // Calculate coordinates as percentages of image dimensions
        const imageWidth = image.offsetWidth;
        const imageHeight = image.offsetHeight;
        
        const width = Math.abs(currentX - startX);
        const height = Math.abs(currentY - startY);
        const left = Math.min(startX, currentX);
        const top = Math.min(startY, currentY);
        
        // Convert to percentage (0-100) for storage
        const xPercent = (left / imageWidth) * 100;
        const yPercent = (top / imageHeight) * 100;
        const widthPercent = (width / imageWidth) * 100;
        const heightPercent = (height / imageHeight) * 100;
        
        // Populate annotation fields
        const annotationX = document.getElementById('annotationX');
        const annotationY = document.getElementById('annotationY');
        const annotationWidth = document.getElementById('annotationWidth');
        const annotationHeight = document.getElementById('annotationHeight');
        if (annotationX) annotationX.value = xPercent.toFixed(2);
        if (annotationY) annotationY.value = yPercent.toFixed(2);
        if (annotationWidth) annotationWidth.value = widthPercent.toFixed(2);
        if (annotationHeight) annotationHeight.value = heightPercent.toFixed(2);
    });

    container.addEventListener('mouseleave', function() {
        isSelecting = false;
    });
}

// Delete annotation
function deleteAnnotation(annotationId) {
    if (!confirm('Are you sure you want to delete this annotation?')) {
        return;
    }

    fetch(`/assets/{{ $asset->id }}/annotations/${annotationId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to delete annotation: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('Error deleting annotation: ' + error.message);
    });
}

// View annotation on image
function viewAnnotationOnImage(x, y, width, height) {
    const modal = document.getElementById('annotationViewerModal');
    const image = document.getElementById('viewerImage');
    const annotationBox = document.getElementById('viewerAnnotationBox');
    
    if (!image || !annotationBox) return;
    
    // Show modal
    modal.style.display = 'flex';
    
    // Wait for image to load and then position annotation box
    image.onload = function() {
        const imageWidth = image.offsetWidth;
        const imageHeight = image.offsetHeight;
        
        // Calculate pixel positions from percentages
        const leftPx = (x / 100) * imageWidth;
        const topPx = (y / 100) * imageHeight;
        const widthPx = (width / 100) * imageWidth;
        const heightPx = (height / 100) * imageHeight;
        
        // Position and size the annotation box
        annotationBox.style.left = leftPx + 'px';
        annotationBox.style.top = topPx + 'px';
        annotationBox.style.width = widthPx + 'px';
        annotationBox.style.height = heightPx + 'px';
        annotationBox.style.display = 'block';
    };
    
    // If image is already loaded, trigger onload
    if (image.complete) {
        image.onload();
    }
}

// Handle escape key to exit fullscreen
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.fullscreenElement) {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
    }
});
</script>
