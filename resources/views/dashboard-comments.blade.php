<x-app-layout>
 <x-slot name="header">
 <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
 New Comments
 </h2>
 </x-slot>

 <div class="py-12">
 <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
 <!-- Back Button -->
 <div class="mb-6">
 <a href="{{ route('dashboard') }}" 
 class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:text-gray-100 :text-gray-200 transition-colors">
 <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
 </svg>
 Back to Dashboard
 </a>
 </div>

 <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
 <div class="p-6">
 <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Recent Comments</h1>
 
 @if($newComments->count() > 0)
 <div class="space-y-4">
 @foreach($newComments as $comment)
 @if($comment->asset)
 <a href="{{ route('assets.show', $comment->asset) }}" class="block p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:bg-gray-900 :bg-gray-600 transition-colors cursor-pointer">
 @else
 <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
 @endif
 <div class="flex items-start space-x-4">
 <div class="flex-shrink-0">
 @if($comment->user)
 <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center">
 <span class="text-purple-600 font-medium">
 {{ strtoupper(substr($comment->user->name, 0, 1)) }}
 </span>
 </div>
 @else
 <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
 <span class="text-gray-500 dark:text-gray-500 font-medium">?</span>
 </div>
 @endif
 </div>
 <div class="flex-1 min-w-0">
 <div class="flex items-center space-x-2 mb-2">
 <span class="text-sm font-semibold text-gray-900 dark:text-gray-100 ">
 {{ $comment->user->name ?? 'Unknown' }}
 </span>
 @if($comment->annotation)
 <span class="px-2 py-1 text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 rounded-full">
 Annotation
 </span>
 @endif
 </div>
 <p class="text-base text-gray-700 dark:text-gray-300 mb-2">
 {{ $comment->text }}
 </p>
 @if($comment->asset && $comment->asset->project)
 <p class="text-sm text-gray-500 dark:text-gray-500 mb-1">
 <span class="font-medium">Asset:</span> {{ $comment->asset->name }}
 </p>
 <p class="text-sm text-gray-500 dark:text-gray-500 mb-1">
 <span class="font-medium">Project:</span> {{ $comment->asset->project->name }}
 </p>
 @if($comment->asset->project->workspace)
 <p class="text-sm text-gray-500 dark:text-gray-500 mb-2">
 <span class="font-medium">Workspace:</span> {{ $comment->asset->project->workspace->name }}
 </p>
 @endif
 @endif
 <p class="text-xs text-gray-400 dark:text-gray-400 ">
 {{ $comment->created_at->format('M d, Y g:i A') }}
 </p>
 </div>
 </div>
 @if($comment->asset)
 </a>
 @else
 </div>
 @endif
 @endforeach
 </div>
 @else
 <div class="text-center py-12">
 <svg class="h-16 w-16 mx-auto text-gray-400 dark:text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
 </svg>
 <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No comments yet</h3>
 <p class="text-gray-500 dark:text-gray-500 ">When people comment on assets, they'll appear here.</p>
 </div>
 @endif
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
