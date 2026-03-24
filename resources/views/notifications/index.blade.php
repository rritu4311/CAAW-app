<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Notifications') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">

                <!-- Success Message -->
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Error Message -->
                @if(session('error'))
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        {{ session('error') }}
                    </div>
                @endif

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
                        
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ $notification->data['message'] ?? $notification->data['title'] ?? 'Notification' }}
                                </p>

                                <p class="text-sm text-gray-500">
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>

                                @if(isset($notification->data['workspace_id']) && is_null($notification->read_at))
                                    <div class="mt-3 flex space-x-2">
                                        <form method="POST" action="{{ route('notifications.approve', $notification->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700 transition-colors">
                                                Approve
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('notifications.reject', $notification->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700 transition-colors">
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                @endif
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