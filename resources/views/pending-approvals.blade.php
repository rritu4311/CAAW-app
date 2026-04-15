<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Pending Approvals') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Pending Approvals
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Review and approve workspace and project access requests.
                </p>
            </div>

            <!-- Workspace Requests -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Workspace Requests
                        <span class="ml-2 text-sm font-normal text-gray-500 dark:text-gray-400">
                            ({{ $pendingWorkspaceUsers->count() }})
                        </span>
                    </h3>
                </div>
                <div class="p-6">
                    @if($pendingWorkspaceUsers->count() > 0)
                        <div class="space-y-4">
                            @foreach($pendingWorkspaceUsers as $pendingUser)
                                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="flex items-center space-x-4">
                                        <div class="h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                            <span class="text-lg font-semibold text-blue-600 dark:text-blue-300">
                                                {{ strtoupper(substr($pendingUser->user->name, 0, 1)) }}
                                            </span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">{{ $pendingUser->user->name }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $pendingUser->user->email }}</p>
                                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                                Requested to join {{ $pendingUser->workspace->name }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        
                                        <form action="{{ route('notifications.reject-workspace', $pendingUser->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400 text-center py-8">
                            No pending workspace requests.
                        </p>
                    @endif
                </div>
            </div>

            <!-- Project Collaborator Requests -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Project Collaborator Requests
                        <span class="ml-2 text-sm font-normal text-gray-500 dark:text-gray-400">
                            ({{ $pendingProjectCollaborators->count() }})
                        </span>
                    </h3>
                </div>
                <div class="p-6">
                    @if($pendingProjectCollaborators->count() > 0)
                        <div class="space-y-4">
                            @foreach($pendingProjectCollaborators as $pendingCollaborator)
                                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="flex items-center space-x-4">
                                        <div class="h-12 w-12 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                                            <span class="text-lg font-semibold text-green-600 dark:text-green-300">
                                                {{ strtoupper(substr($pendingCollaborator->user->name, 0, 1)) }}
                                            </span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">{{ $pendingCollaborator->user->name }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $pendingCollaborator->user->email }}</p>
                                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                                Requested to join {{ $pendingCollaborator->project->name }} as {{ ucfirst($pendingCollaborator->role) }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                      
                                        <form action="{{ route('notifications.reject-project', $pendingCollaborator->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400 text-center py-8">
                            No pending project collaborator requests.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
