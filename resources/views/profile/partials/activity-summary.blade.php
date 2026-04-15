<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Recent Activity') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Your last 10 activities.') }}
        </p>
    </header>

    <div class="mt-6 space-y-4">
        @if($recentActivities->count() > 0)
            @foreach($recentActivities as $activity)
                <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ ucfirst($activity->description) }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ $activity->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('No recent activity.') }}
                </p>
            </div>
        @endif
    </div>
</section>
