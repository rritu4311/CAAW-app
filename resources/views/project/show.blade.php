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
                        
                        <!-- Action Buttons -->
                        <div class="flex gap-2">
                            @if($project->isOwnedBy(auth()->user()))
                                <a href="{{ route('projects.share', $project) }}" 
                                   class="p-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors" title="Share Project">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"/>
                                    </svg>
                                </a>
                            @endif
                            @if(!isset($readOnly) || !$readOnly)
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
                                <!-- Archive Button -->
                                    <button type="button" 
                                            class="bg-red-600 text-white p-2 rounded hover:bg-red-700 transition"
                                            onclick="event.stopPropagation()"
                                            title="Archive Project">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                        </svg>
                                    </button>
                            @endif
                        </div>
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