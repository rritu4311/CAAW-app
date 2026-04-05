<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Workspace Archive') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white dark:bg-gray-800">
                    <div class="mb-6">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                            {{ $workspace->name }} Archive
                        </h1>
                        <p class="text-gray-600 dark:text-gray-400">
                            View and manage archived projects from this workspace.
                        </p>
                    </div>

                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($projects->count() === 0)
                        <div class="text-center py-12">
                            <div class="w-20 h-20 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                                No Archived Projects
                            </h3>
                            <p class="text-gray-500 dark:text-gray-400">
                                This workspace has no archived projects.
                            </p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($projects as $project)
                                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 hover:shadow-md transition-shadow">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-12 h-12 bg-red-500 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="flex items-center space-x-2">
                                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                    {{ $project->name }}
                                                </h3>
                                                <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                    Archived
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                @if($project->client_name)
                                                    Client: {{ $project->client_name }} |
                                                @endif
                                                {{ $project->folders->count() }} folders
                                            </p>
                                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                                Archived on {{ $project->updated_at->format('M d, Y') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('projects.show', $project) }}" 
                                           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition duration-200 text-sm">
                                            View
                                        </a>
                                        <form method="POST" action="{{ route('projects.unarchive', $project) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition duration-200 text-sm"
                                                    onclick="return confirm('Are you sure you want to unarchive this project?')">
                                                Unarchive
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="mt-8 flex justify-between">
                        <a href="{{ route('workspace.page', $workspace) }}" 
                           class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                            ← Back to Workspace
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
