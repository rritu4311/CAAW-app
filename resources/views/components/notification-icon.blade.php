@php
    $unreadCount = auth()->user()->unreadNotifications()->count();
@endphp

<div x-data="{ 
    unreadCount: {{ $unreadCount }}
}" 
class="relative">

    <!-- Notification Bell Icon - Link to Notification Page -->
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
        <span x-show="unreadCount > 0" 
              x-text="unreadCount > 99 ? '99+' : unreadCount"
              class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center animate-pulse">
        </span>
    </a>
</div>
