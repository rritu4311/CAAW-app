<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Shared Workspaces') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white dark:bg-gray-800">
                    <div class="mb-6">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                            Workspaces Shared With You
                        </h1>
                        <p class="text-gray-600 dark:text-gray-400">
                            Workspaces that have been shared with you and you have access to.
                        </p>
                    </div>

                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
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
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach($pendingWorkspaces as $workspace)
                                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-6">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                </svg>
                                            </div>
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                Pending
                                            </span>
                                        </div>
                                        
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                                            {{ $workspace->name }}
                                        </h3>
                                        
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                            Invited by {{ $workspace->owner->name }}
                                        </p>
                                        
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                            Waiting for your approval
                                        </p>
                                        
                                        <a href="{{ route('notifications.index') }}" 
                                           class="block w-full bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 text-center">
                                            View Invitation
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($workspaces->count() === 0 && $pendingWorkspaces->count() === 0)
                        <div class="text-center py-12">
                            <div class="w-20 h-20 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                                No Workspaces Shared With You
                            </h3>
                            <p class="text-gray-500 dark:text-gray-400">
                                When someone shares a workspace with you, it will appear here.
                            </p>
                        </div>
                    @else
                        @if($workspaces->count() > 0)
                            <div class="mb-6">
                                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                                    <span class="inline-flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        My Workspaces ({{ $workspaces->count() }})
                                    </span>
                                </h2>
                            </div>
                        @endif
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($workspaces as $workspace)
                                @php
                                    $workspaceUser = $workspace->workspaceUsers->where('user_id', auth()->user()->id)->first();
                                    $userRole = $workspaceUser ? $workspaceUser->role : 'member';
                                @endphp
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 hover:shadow-lg transition-shadow duration-200">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                        </div>
                                        <span class="px-2 py-1 text-xs font-medium rounded-full
                                            {{ $userRole === 'admin' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 
                                               'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' }}">
                                            {{ ucfirst($userRole) }}
                                        </span>
                                    </div>
                                    
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                                        {{ $workspace->name }}
                                    </h3>
                                    
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                        Shared by {{ $workspace->owner->name }}
                                    </p>
                                    
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                        Created {{ $workspace->created_at->diffForHumans() }}
                                    </p>
                                    
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            <span class="font-medium">{{ $workspace->workspaceUsers->where('status', 'approved')->count() }}</span>
                                            members
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            <span class="font-medium">{{ $workspace->projects->count() }}</span>
                                            projects
                                        </div>
                                    </div>
                                    
                                    <div class="flex space-x-2">
                                        <a href="{{ route('workspaces.show', $workspace) }}" 
                                           class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 text-center">
                                            Open Workspace
                                        </a>
                                    </div>
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
