@php
    $unreadCount = auth()->user()->unreadNotifications()->count();
    $recentNotifications = auth()->user()->notifications()->orderBy('created_at', 'desc')->limit(5)->get();
@endphp

<div class="relative" x-data="{ open: false }">

    <!-- Notification Bell Icon - Dropdown Toggle -->
    <button @click="open = !open" 
            class="relative p-2 text-white hover:bg-white/10 rounded-lg transition-colors duration-200 inline-flex items-center"
            title="Notifications">
        
        <!-- Bell Icon -->
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
            </path>
        </svg>
        
        <!-- Notification Badge -->
        @if($unreadCount > 0)
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center animate-pulse">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    <!-- Notifications Dropdown -->
    <div x-show="open" 
         x-cloak
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50">
        
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Notifications</h3>
            <div class="flex space-x-2">
                @if($unreadCount > 0)
                    <form method="POST" action="{{ route('notifications.mark-all-read') }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400">
                            Mark all read
                        </button>
                    </form>
                @endif
                <a href="{{ route('notifications.index') }}" class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400">
                    View all
                </a>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="max-h-96 overflow-y-auto">
            @forelse ($recentNotifications as $notification)
                <div class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700 last:border-b-0 {{ is_null($notification->read_at) ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}">
                    <div class="flex justify-between items-start">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                {{ $notification->data['message'] ?? $notification->data['title'] ?? 'Notification' }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                            
                            @if(isset($notification->data['workspace_id']) && is_null($notification->read_at))
                                <div class="mt-2 flex space-x-1">
                                    <form method="POST" action="{{ route('notifications.approve', $notification->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 transition-colors">
                                            Approve
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('notifications.reject', $notification->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 transition-colors">
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                        <div class="ml-2 flex-shrink-0">
                            @if (is_null($notification->read_at))
                                <form method="POST" action="{{ route('notifications.mark-read', $notification->id) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-blue-600 hover:text-blue-800 dark:text-blue-400" title="Mark as read">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-6 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">No notifications yet</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

@php
function getNotificationMessage($notification) {
    $data = $notification->data;
    
    // Handle workspace invitation notifications
    if (isset($data['workspace_name'])) {
        return "You've been invited to join workspace: {$data['workspace_name']}";
    }
    
    // Handle generic message
    if (isset($data['message'])) {
        return $data['message'];
    }
    
    // Handle title fallback
    if (isset($data['title'])) {
        return $data['title'];
    }
    
    return 'New notification';
}
@endphp
