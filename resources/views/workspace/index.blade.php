<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('WorkSpace') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white dark:bg-gray-800">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <p class="text-gray-600 dark:text-gray-400 mt-1">
                                <span class="font-bold text-xl">Workspace:</span> 
                                <span class="font-bold text-xl">{{ $workspace->name }}</span>
                            </p>
                        </div>
                        @if($isOwner || $isWorkspaceUser)
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('workspace.create', $workspace) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                                Create Project
                            </a>
                        </div>
                        @elseif($isAdmin && !$isOwner)
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('workspace.unarchive', $workspace) }}" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004 12v1m4.21-12l3 3m-3-3l-3 3m2.9 13.9a8.001 8.001 0 0011.319 0l1.414 1.414A10.001 10.001 0 0112 21c-3.217 0-6.32-1.28-8.54-3.52l1.42-1.42z"/>
                                </svg>
                                Unarchive
                            </a>
                            <a href="{{ route('workspace.archive', $workspace) }}" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                </svg>
                                Archive
                            </a>
                        </div>
                        @endif
                    </div>

                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                            {{ session('success') }}
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

                    @php
                        $activeProjects = $workspace->projects->filter(function($project) {
                            return $project->status !== 'archived';
                        });
                    @endphp
                    <div class="space-y-6">
                        @if($activeProjects->count() === 0)
                            <div class="col-span-full text-center py-12">
                                <p class="text-gray-500 text-lg">No projects found. Create your first project!</p>
                            </div>
                        @else
                            @foreach($activeProjects as $project)
                                <div onclick="window.location.href='{{ route('projects.show', $project) }}'"
                                     class="bg-white dark:bg-gray-700 rounded-lg shadow-md hover:shadow-xl transition-all duration-300 ease-in-out p-6 border border-gray-200 dark:border-gray-600 cursor-pointer">
                                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between mb-3">
                                                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $project->name }}</h3>
                                                <div class="flex items-center space-x-2">
                                                    @php
                                                        $pendingCount = $project->pendingApprovalsCount();
                                                    @endphp
                                                    @if($pendingCount > 0)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200" title="{{ $pendingCount }} pending approval{{ $pendingCount > 1 ? 's' : '' }}">
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                            </svg>
                                                            {{ $pendingCount }} pending
                                                        </span>
                                                    @endif
                                                    @if($isOwner || $isWorkspaceUser || $project->isOwnedBy(auth()->user()))
                                                    <form action="{{ route('workspace.edit', [$workspace, $project]) }}" method="GET" class="inline">
                                                        <button type="submit" 
                                                                class="bg-blue-600 dark:bg-blue-500 hover:bg-blue-700 dark:hover:bg-blue-600 text-white p-2 rounded transition duration-200 border border-blue-700 dark:border-blue-600"
                                                                onclick="event.stopPropagation()">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('workspace.destroy', [$workspace, $project]) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this project?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="bg-red-600 dark:bg-red-500 hover:bg-red-700 dark:hover:bg-red-600 text-white p-2 rounded transition duration-200 border border-red-700 dark:border-red-600"
                                                                onclick="event.stopPropagation()">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @elseif($isAdmin && !$isOwner)
                                                    <form method="POST" action="{{ route('projects.archive', $project) }}" class="inline" onsubmit="return confirm('Are you sure you want to archive this project?')">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit"
                                                                class="bg-red-600 text-white p-2 rounded hover:bg-red-700 transition"
                                                                title="Archive Project"
                                                                onclick="event.stopPropagation()">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                @endif
                                                </div>
                                            </div>
                                            <div class="space-y-2 mb-4">
                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                    <strong>Client:</strong> {{ $project->client_name }}
                                                </p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                    <strong>Deadline:</strong> {{ $project->deadline ? $project->deadline->format('M d, Y') : 'Not set' }}
                                                </p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                                                    {{ $project->description }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
