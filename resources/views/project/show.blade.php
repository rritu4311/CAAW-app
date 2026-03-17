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
                            <h1 class="text-3xl font-bold mb-4">{{ $project->name }}</h1>
                        </div>
                        
                        <!-- Create Root Folder Button -->
                        <button onclick="openModal('createFolderModalroot')" 
                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                            Create Folder
                        </button>
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
                            <x-folder-tree :folders="$folders" :projectId="$project->id" />
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Create Folder Modal -->
    <x-create-folder-modal :projectId="$project->id" />

</x-app-layout>