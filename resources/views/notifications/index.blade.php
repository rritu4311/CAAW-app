<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Notifications') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">

                <div class="flex justify-between mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Notifications</h1>

                    <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                        @csrf
                        @method('PATCH')
                        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg">
                            Mark All as Read
                        </button>
                    </form>
                </div>

                @forelse ($notifications as $notification)
                    <div class="border-b p-4 {{ is_null($notification->read_at) ? 'bg-blue-50' : '' }}">
                        
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ $notification->data['message'] ?? $notification->data['title'] ?? 'Notification' }}
                                </p>

                                <p class="text-sm text-gray-500">
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                            </div>

                            @if (is_null($notification->read_at))
                                <form method="POST" action="{{ route('notifications.mark-read', $notification->id) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button class="text-sm text-blue-600">
                                        Mark as Read
                                    </button>
                                </form>
                            @endif
                        </div>

                    </div>
                @empty
                    <div class="text-center py-12">
                        <p class="text-gray-500">You're all caught up!</p>
                    </div>
                @endforelse

            </div>
        </div>
    </div>
</x-app-layout>