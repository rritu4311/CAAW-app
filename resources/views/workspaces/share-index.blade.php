<x-app-layout>
 <x-slot name="header">
 <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
 {{ __('Shared Workspaces') }}
 </h2>
 </x-slot>

 <div class="py-12">
 <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
 <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
 <div class="p-6 bg-white dark:bg-gray-800 ">
 <div class="mb-6">
 <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">
 Workspaces Shared With You
 </h1>
 <p class="text-gray-600 dark:text-gray-400 ">
 Workspaces that have been shared with you and you have access to.
 </p>
 </div>

 @if(session('success'))
 <div class="mb-4 p-4 bg-green-100 dark:bg-green-900 border border-green-400 text-green-700 rounded">
 {{ session('success') }}
 </div>
 @endif

 <!-- Pending Invitations Section -->
 @if($pendingWorkspaces->count() > 0)
 <div class="mb-8">
 <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">
 <span class="inline-flex items-center">
 <svg class="w-5 h-5 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
 </svg>
 Pending Invitations ({{ $pendingWorkspaces->count() }})
 </span>
 </h2>
 <div class="space-y-3">
 @foreach($pendingWorkspaces as $workspace)
 <div class="flex items-center justify-between p-4 bg-yellow-50 /20 border border-yellow-200 rounded-lg">
 <div class="flex items-center space-x-4">
 <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center flex-shrink-0">
 <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
 </svg>
 </div>
 <div>
 <div class="flex items-center gap-3 mb-1">
 <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 ">
 {{ $workspace->name }}
 </h3>
 <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 dark:bg-yellow-900 text-yellow-800 ">
 Pending
 </span>
 </div>
 <p class="text-sm text-gray-600 dark:text-gray-400 ">
 Invited by {{ $workspace->owner->name }} - Waiting for your approval
 </p>
 </div>
 </div>
 <a href="{{ route('notifications.index') }}" 
 class="bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 whitespace-nowrap">
 View Invitation
 </a>
 </div>
 @endforeach
 </div>
 </div>
 @endif

 @if($workspaces->count() === 0 && $pendingWorkspaces->count() === 0)
 <div class="text-center py-12">
 <div class="w-20 h-20 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
 <svg class="w-10 h-10 text-gray-400 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
 </svg>
 </div>
 <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
 No Workspaces Shared With You
 </h3>
 <p class="text-gray-500 dark:text-gray-500 ">
 When someone shares a workspace with you, it will appear here.
 </p>
 </div>
 @else
 @if($workspaces->count() > 0)
 <div class="mb-6">
 <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 ">
 <span class="inline-flex items-center">
 <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
 </svg>
 My Workspaces ({{ $workspaces->count() }})
 </span>
 </h2>
 </div>
 @endif
 <div class="space-y-3">
 @foreach($workspaces as $workspace)
 @php
 $workspaceUser = $workspace->workspaceUsers->where('user_id', auth()->user()->id)->first();
 $userRole = $workspaceUser ? $workspaceUser->role : 'member';
 @endphp
 <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow duration-200 cursor-pointer" onclick="window.location='{{ route('workspaces.show', $workspace) }}'">
 <div class="flex items-center space-x-4">
 <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center flex-shrink-0">
 <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
 </svg>
 </div>
 <div>
 <div class="flex items-center gap-3 mb-1">
 <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 ">
 {{ $workspace->name }}
 </h3>
 <span class="px-2 py-1 text-xs font-medium rounded-full
 {{ $userRole === 'admin' ? 'bg-blue-100 dark:bg-blue-900 text-blue-800 ' : 
 'bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 ' }}">
 {{ ucfirst($userRole) }}
 </span>
 </div>
 <p class="text-sm text-gray-600 dark:text-gray-400 ">
 Shared by {{ $workspace->owner->name }} - Created {{ $workspace->created_at->diffForHumans() }}
 </p>
 <div class="flex items-center gap-4 mt-1 text-sm text-gray-500 dark:text-gray-500 ">
 <span><span class="font-medium">{{ $workspace->workspaceUsers->where('status', 'approved')->count() }}</span> members</span>
 <span><span class="font-medium">{{ $workspace->projects->count() }}</span> projects</span>
 </div>
 </div>
 </div>
 @if($userRole === 'admin')
 <a href="{{ route('workspaces.share', $workspace) }}" onclick="event.stopPropagation()"
 class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200"
 title="Share Workspace">
 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"/>
 </svg>
 </a>
 @endif
 </div>
 @endforeach
 </div>
 @endif

 <!-- Actions -->
 <div class="mt-8 flex justify-between">
 <a href="{{ route('workspaces.page') }}" 
 class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
 ← Back to Workspaces
 </a>
 </div>
 </div>
 </div>
 </div>
 </div>
</x-app-layout>
