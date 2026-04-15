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

                    @if($activities->count() > 0)
                        <div class="space-y-6">
                            @foreach($activities as $activity)
                                @if($activity->subject)
                                <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg shadow-sm">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            @php
                                                $subject = $activity->subject;
                                                $subjectType = get_class($subject);
                                            @endphp

                                            @if($subjectType === 'App\Models\Workspace')
                                                <div class="flex items-center mb-2">
                                                    <div class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-2 py-1 rounded text-sm font-medium mr-3">
                                                        Workspace
                                                    </div>
                                                    <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ $subject->name }}</h4>
                                                </div>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                    {{ $activity->description }}
                                                </p>
                                            @elseif($subjectType === 'App\Models\Project')
                                                <div class="flex items-center mb-2">
                                                    <div class="bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-2 py-1 rounded text-sm font-medium mr-3">
                                                        Project
                                                    </div>
                                                    <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ $subject->name }}</h4>
                                                </div>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                    {{ $activity->description }}
                                                </p>
                                            @elseif($subjectType === 'App\Models\Folder')
                                                <div class="flex items-center mb-2">
                                                    <div class="bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 px-2 py-1 rounded text-sm font-medium mr-3">
                                                        Folder
                                                    </div>
                                                    <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ $subject->name }}</h4>
                                                </div>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                    {{ $activity->description }}
                                                </p>
                                            @elseif($subjectType === 'App\Models\Asset')
                                                <div class="flex items-center mb-2">
                                                    <div class="bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 px-2 py-1 rounded text-sm font-medium mr-3">
                                                        Asset
                                                    </div>
                                                    <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ $subject->name }}</h4>
                                                </div>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                    {{ $activity->description }}
                                                </p>
                                            @else
                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                    {{ $activity->description }}
                                                </p>
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400 ml-4">
                                            {{ $activity->created_at->setTimezone('Asia/Kolkata')->format('M d, Y H:i') }}
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400 text-center py-8">No activities found.</p>
                    @endif

                    <!-- Pagination Links -->
                    @if($activities->hasPages())
                        <div class="mt-8 flex justify-end">
                            {{ $activities->links() }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
