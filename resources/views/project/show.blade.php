<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Project') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white dark:bg-gray-800">

                    <!-- Back Button -->
                    <div class="mb-4">
                        <a href="{{ route('workspaces.show', $project->workspace) }}" 
                           class="inline-flex items-center text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to {{ $project->workspace->name }}
                        </a>
                    </div>

                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <div class="flex items-center gap-3 mb-4">
                                <h1 class="text-3xl font-bold">{{ $project->name }}</h1>
                                @if($project->status === 'archived')
                                    <span class="inline-flex items-center px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-medium" title="Archived">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                        </svg>
                                        Archived
                                    </span>
                                @endif
                                @if(isset($readOnly) && $readOnly)
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">Read-Only (Workspace Admin)</span>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Owner Action Buttons -->
                        @if(($project->isOwnedBy(auth()->user()) || auth()->user()->isWorkspaceOwner($project) || auth()->user()->hasWorkspaceRole($project->workspace, ['user'])) && !$readOnly)
                            <div class="flex gap-2">
                                <!-- Share Button -->
                                <a href="{{ route('projects.share', $project) }}"
                                   class="p-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors" title="Share Project">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"/>
                                    </svg>
                                </a>

                                <!-- Create Folder Button -->
                                <button onclick="openModal('createFolderModalroot')"
                                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors" title="Create Folder">
                                    Create Folder
                                </button>

                                <!-- Delete Project Icon -->
                                <form action="{{ route('workspace.destroy', [$project->workspace, $project]) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this project?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors" title="Delete Project">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        @endif

                        <!-- Admin Action Buttons -->
                        @if(auth()->user()->isWorkspaceAdmin($project) && !auth()->user()->isWorkspaceOwner($project))
                            <div class="flex gap-2">
                                <!-- Archive/Unarchive Button -->
                                @if($project->status === 'archived')
                                    <form method="POST" action="{{ route('projects.unarchive', $project) }}" class="inline" onsubmit="return confirm('Are you sure you want to unarchive this project?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="bg-green-600 text-white p-2 rounded hover:bg-green-700 transition"
                                                title="Unarchive Project">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004 12v1m4.21-12l3 3m-3-3l-3 3m2.9 13.9a8.001 8.001 0 0011.319 0l1.414 1.414A10.001 10.001 0 0112 21c-3.217 0-6.32-1.28-8.54-3.52l1.42-1.42z"/>
                                            </svg>
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('projects.archive', $project) }}" class="inline" onsubmit="return confirm('Are you sure you want to archive this project?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="bg-red-600 text-white p-2 rounded hover:bg-red-700 transition"
                                                title="Archive Project">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Project Details -->
                    <div class="bg-gray-50 shadow rounded-lg p-6 space-y-4 mb-6">
                        <p><strong>Client:</strong> {{ $project->client_name }}</p>
                        <p><strong>Description:</strong> {{ $project->description }}</p>
                        <p><strong>Deadline:</strong> {{ $project->deadline }}</p>
                        <p><strong>Created At:</strong> {{ $project->created_at }}</p>
                    </div>

                    <!-- Folder Tree Section -->
                    <div class="bg-white border rounded-lg p-6">
                        <h2 class="text-xl font-semibold mb-4 text-gray-800">Project Folders</h2>
                        
                        @if(session('success'))
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                                {{ session('success') }}
                            </div>
                        @endif

                        <div class="folder-tree">
                            <x-folder-tree :folders="$folders" :projectId="$project->id" :readOnly="isset($readOnly) ? $readOnly : false" />
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Create Folder Modal -->
    <x-create-folder-modal :projectId="$project->id" />
    
    <!-- Edit Folder Modal -->
    <x-edit-folder-modal />

</x-app-layout>