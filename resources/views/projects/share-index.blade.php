<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Shared Projects') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white dark:bg-gray-800">
                    <div class="mb-6">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                            Projects Shared With You
                        </h1>
                        <p class="text-gray-600 dark:text-gray-400">
                            Projects that have been shared with you and you have access to.
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                                No Projects Shared With You
                            </h3>
                            <p class="text-gray-500 dark:text-gray-400">
                                When someone shares a project with you, it will appear here.
                            </p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($projects as $project)
                                @php
                                    $collaborator = $project->projectCollaborators->where('user_id', auth()->user()->id)->first();
                                    $userRole = $collaborator ? $collaborator->role : 'viewer';
                                @endphp
                                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 hover:shadow-md transition-shadow cursor-pointer" onclick="window.location='{{ route('projects.show', $project) }}'">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-12 h-12 bg-green-500 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="flex items-center space-x-2">
                                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                    {{ $project->name }}
                                                </h3>
                                                <span class="px-2 py-1 text-xs font-medium
                                                    {{ $userRole === 'admin' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 
                                                       ($userRole === 'reviewer' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' :
                                                       'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200') }}">
                                                    {{ ucfirst($userRole) }}
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                @if($project->client_name)
                                                    Client: {{ $project->client_name }} |
                                                @endif
                                                Shared by {{ $project->owner->name }} | 
                                                {{ $project->projectCollaborators->where('status', 'approved')->count() }} collaborators |
                                                {{ $project->folders->count() }} folders
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="mt-8 flex justify-between">
                        <a href="{{ route('dashboard') }}" 
                           class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                            ← Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
