<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Unarchive Projects - {{ $workspace->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white dark:bg-gray-800">
                    <div class="mb-6">
                        <a href="{{ route('workspace.page', $workspace) }}" 
                           class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                            &larr; Back to Workspace
                        </a>
                    </div>

                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                        Archived Projects
                    </h1>

                    @if($projects->count() === 0)
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No archived projects</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                There are no archived projects in this workspace.
                            </p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($projects as $project)
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 border border-gray-200 dark:border-gray-600">
                                    <div class="flex justify-between items-start mb-4">
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $project->name }}</h3>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            Archived
                                        </span>
                                    </div>
                                    
                                    <div class="space-y-2 mb-4">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            <strong>Client:</strong> {{ $project->client_name }}
                                        </p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            <strong>Deadline:</strong> {{ $project->deadline ? \Carbon\Carbon::parse($project->deadline)->format('M d, Y') : 'N/A' }}
                                        </p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            <strong>Created:</strong> {{ $project->created_at->format('M d, Y') }}
                                        </p>
                                    </div>

                                    <div class="flex space-x-3">
                                        <a href="{{ route('workspace.show', [$workspace, $project]) }}" 
                                           class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg text-center transition duration-200">
                                            View Details
                                        </a>
                                        
                                        <form method="POST" action="{{ route('projects.unarchive', $project) }}" class="inline" onsubmit="return confirm('Are you sure you want to unarchive this project?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200 flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004 12v1m4.21-12l3 3m-3-3l-3 3m2.9 13.9a8.001 8.001 0 0011.319 0l1.414 1.414A10.001 10.001 0 0112 21c-3.217 0-6.32-1.28-8.54-3.52l1.42-1.42z"/>
                                                </svg>
                                                Unarchive
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
