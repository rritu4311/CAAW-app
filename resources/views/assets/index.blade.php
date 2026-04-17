<x-app-layout>
 <x-slot name="header">
 <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
 {{ __('All Assets') }}
 </h2>
 </x-slot>

 <div class="py-12">
 <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
 <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl overflow-hidden">
 
 <!-- Header -->
 <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-700 ">
 <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 ">
 Assets from Your Workspaces
 </h3>
 <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
 Showing all assets from projects you have access to
 </p>
 </div>

 <!-- Assets List -->
 <div class="p-6">
 @if($assets->count() > 0)
 <div class="space-y-3">
 @foreach($assets as $asset)
 <div class="flex items-center p-4 bg-gray-50 dark:bg-gray-700 border rounded-lg hover:shadow-md transition-shadow">
 <!-- Icon/Thumbnail -->
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
 <div class="w-12 h-12 rounded-lg bg-gray-200 flex items-center justify-center">
 <svg class="w-6 h-6 text-gray-500 dark:text-gray-500" fill="currentColor" viewBox="0 0 20 20">
 <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
 </svg>
 </div>
 @endif
 </div>
 
 <!-- File Info -->
 <div class="flex-1 min-w-0 mr-4">
 <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 truncate" title="{{ $asset->name }}">{{ $asset->name }}</h4>
 <p class="text-xs text-gray-500 dark:text-gray-500 ">
 {{ $asset->formatted_size }} • {{ strtoupper($asset->file_type) }}
 </p>
 @if($asset->folder && $asset->folder->project)
 <p class="text-xs text-gray-400 dark:text-gray-400 mt-1">
 {{ $asset->folder->project->name }} / {{ $asset->folder->name }}
 </p>
 @elseif($asset->folder && $asset->folder->project)
 <p class="text-xs text-gray-400 dark:text-gray-400 mt-1">
 {{ $asset->folder->project->name }} / Project Root
 </p>
 @endif
 <p class="text-xs text-gray-400 dark:text-gray-400 mt-1">
 Uploaded by {{ $asset->uploadedBy->name ?? 'Unknown' }} • {{ $asset->created_at->format('M d, Y') }}
 </p>
 </div>
 
 <!-- Actions -->
 <div class="flex items-center gap-2 flex-shrink-0">
 @if(in_array($asset->file_type, ['image', 'video', 'pdf', 'doc', 'docx', 'xlsx', 'xls', 'csv', 'txt', 'md', 'markdown']))
 <a href="{{ route('assets.show', $asset->id) }}" 
 class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded transition-colors">View</a>
 @endif
 <a href="{{ Storage::url($asset->file_path) }}" download="{{ $asset->name }}" 
 class="px-3 py-1.5 bg-gray-100 dark:bg-gray-900 hover:bg-gray-200 :bg-gray-600 text-gray-700 dark:text-gray-300 text-xs font-medium rounded transition-colors">Download</a>
 </div>
 </div>
 @endforeach
 </div>
 
 <!-- Pagination -->
 {{ $assets->links() }}
 @else
 <div class="text-center py-12">
 <div class="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
 <svg class="w-12 h-12 text-gray-400 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
 </svg>
 </div>
 <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">No Assets Found</h3>
 <p class="text-gray-600 dark:text-gray-400 mb-6">
 You don't have any assets in your workspaces yet.
 </p>
 <a href="{{ route('workspaces.page') }}" 
 class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
 </svg>
 Go to Workspaces
 </a>
 </div>
 @endif
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
