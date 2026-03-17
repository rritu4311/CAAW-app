<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Folder') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white dark:bg-gray-800">

                    <!-- Breadcrumb Navigation -->
                    <nav class="flex items-center space-x-2 text-sm mb-6">
                        <a href="{{ route('projects.show', $folder->project->id) }}" class="text-blue-600 hover:text-blue-800">
                            {{ $folder->project->name }}
                        </a>
                        <span class="text-gray-400">/</span>
                        @foreach($breadcrumbs as $index => $breadcrumb)
                            @if($index < count($breadcrumbs) - 1)
                                <a href="{{ route('folders.show', $breadcrumb->id) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $breadcrumb->name }}
                                </a>
                                <span class="text-gray-400">/</span>
                            @else
                                <span class="text-gray-700 font-medium">{{ $breadcrumb->name }}</span>
                            @endif
                        @endforeach
                    </nav>

                    <!-- Folder Header -->
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h1 class="text-3xl font-bold mb-2 flex items-center">
                                <svg class="w-8 h-8 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                </svg>
                                {{ $folder->name }}
                            </h1>
                            <p class="text-gray-600">
                                Created: {{ $folder->created_at->format('M d, Y') }} | 
                                {{ $folder->children->count() }} subfolder(s)
                            </p>
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex space-x-2">
                            <!-- Create Subfolder Button -->
                            <button onclick="openModal('createFolderModal{{ $folder->id }}')" 
                                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                                Create Subfolder
                            </button>
                            
                            <!-- Delete Folder Button -->
                            <form action="{{ route('folders.destroy', $folder->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this folder and all its contents?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                                    Delete Folder
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Success Message -->
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Folder Contents -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h2 class="text-xl font-semibold mb-4 text-gray-800">Folder Contents</h2>
                        
                        @if($folder->children->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($folder->children as $child)
                                    <div class="bg-white border rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer">
                                        <a href="{{ route('folders.show', $child->id) }}" class="block">
                                            <div class="flex items-center mb-2">
                                                <svg class="w-6 h-6 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                                </svg>
                                                <span class="font-medium text-gray-800">{{ $child->name }}</span>
                                            </div>
                                            <p class="text-sm text-gray-600">
                                                {{ $child->children->count() }} subfolder(s)
                                            </p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                Created {{ $child->created_at->format('M d, Y') }}
                                            </p>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12">
                                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                </svg>
                                <p class="text-gray-500 text-lg">No subfolders yet</p>
                                <p class="text-gray-400">Create your first subfolder using the button above</p>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Create Subfolder Modal -->
    <x-create-folder-modal :projectId="$folder->project->id" :parentFolderId="$folder->id" />

</x-app-layout>
