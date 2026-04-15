@php
    $unreadCount = auth()->user()->unreadNotifications()->count();
@endphp

<!-- Notification Icon - Direct Link to Full View -->
<a href="{{ route('notifications.index') }}" 
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
        <span class="absolute -top-1 -right-1 bg-red-500 text-red-500 text-xs font-bold rounded-full h-6 w-6 flex items-center justify-center animate-pulse">
            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
        </span>
    @endif
</a>

