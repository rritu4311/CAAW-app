<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Activity Log') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    @if($paginatedActivities->count() > 0)
                        <div class="space-y-6">
                            @foreach($paginatedActivities as $activity)
                                <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg shadow-sm">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            @if($activity->type === 'workspace')
                                                <div class="flex items-center mb-2">
                                                    <div class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-2 py-1 rounded text-sm font-medium mr-3">
                                                        Workspace
                                                    </div>
                                                    <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ $activity->name }}</h4>
                                                </div>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                                    Owner: {{ $activity->owner->name }}
                                                </p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                    Created a new workspace
                                                </p>
                                            @elseif($activity->type === 'project')
                                                <div class="flex items-center mb-2">
                                                    <div class="bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-2 py-1 rounded text-sm font-medium mr-3">
                                                        Project
                                                    </div>
                                                    <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ $activity->name }}</h4>
                                                </div>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                                    Workspace: {{ $activity->workspace->name }}
                                                </p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                                                    Client: {{ $activity->client_name ?? 'N/A' }}
                                                </p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                    Created a new project
                                                </p>
                                            @elseif($activity->type === 'folder')
                                                <div class="flex items-center mb-2">
                                                    <div class="bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 px-2 py-1 rounded text-sm font-medium mr-3">
                                                        Folder
                                                    </div>
                                                    <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ $activity->name }}</h4>
                                                </div>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                                    Project: {{ $activity->project->name }}
                                                </p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                                                    Parent Folder: {{ $activity->parent ? $activity->parent->name : 'Root' }}
                                                </p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                    Created a new folder
                                                </p>
                                            @elseif($activity->type === 'asset')
                                                <div class="flex items-center mb-2">
                                                    <div class="bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 px-2 py-1 rounded text-sm font-medium mr-3">
                                                        Asset
                                                    </div>
                                                    <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ $activity->name ?? $activity->filename }}</h4>
                                                </div>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                                    Folder: {{ $activity->folder->name }}
                                                </p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                                                    Type: {{ $activity->file_type ?? $activity->mime_type }}
                                                </p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                                                    Size: {{ $activity->formatted_size_attribute }}
                                                </p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                    Uploaded a new file
                                                </p>
                                                @if($activity->isImage())
                                                    <div class="mt-3">
                                                        <img src="{{ asset('storage/' . $activity->path) }}" alt="{{ $activity->name }}" class="max-w-xs h-32 object-cover rounded border">
                                                    </div>
                                                @elseif($activity->isVideo())
                                                    <div class="mt-3">
                                                        <video class="max-w-xs h-32 rounded border">
                                                            <source src="{{ asset('storage/' . $activity->path) }}" type="{{ $activity->mime_type }}">
                                                        </video>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400 ml-4">
                                            {{ $activity->created_at->setTimezone('Asia/Kolkata')->format('M d, Y H:i') }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400 text-center py-8">No activities found.</p>
                    @endif

                    <!-- Pagination Links -->
                    @if($paginatedActivities->hasPages())
                        <div class="mt-8 flex justify-end">
                            {{ $paginatedActivities->links() }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
