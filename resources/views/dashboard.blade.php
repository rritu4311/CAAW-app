<x-app-layout>
 <x-slot name="header">
 <h2 class="font-semibold text-xl text-gray-800 leading-tight">
 {{ __('Dashboard') }}
 </h2>
 </x-slot>

 <div class="py-12">
 <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
 <!-- Welcome Message -->
 <div class="mb-8">
 <h1 class="text-3xl font-bold text-gray-900 ">
 Welcome back, {{ auth()->user()->name }}!
 </h1>
 <p class="mt-2 text-gray-600 ">
 Here's what's happening with your projects today.
 </p>
 </div>

 <!-- Quick Stats Cards -->
 <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
 <!-- Pending Approvals -->
 <a href="{{ route('dashboard.pending-approvals') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition-shadow cursor-pointer">
 <div class="flex items-center justify-between">
 <div>
 <p class="text-sm font-medium text-gray-500 ">Pending Approvals</p>
 <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['pending_approvals'] }}</p>
 </div>
 <div class="flex-shrink-0">
 <div class="h-12 w-12 rounded-full bg-yellow-100 flex items-center justify-center">
 <svg class="h-6 w-6 text-yellow-600 " fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
 </svg>
 </div>
 </div>
 </div>
 </a>

 <!-- Assets in Review -->
 <a href="{{ route('assets.index') }}?status=in_review" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition-shadow cursor-pointer">
 <div class="flex items-center justify-between">
 <div>
 <p class="text-sm font-medium text-gray-500 ">Assets in Review</p>
 <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['assets_in_review'] }}</p>
 </div>
 <div class="flex-shrink-0">
 <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
 <svg class="h-6 w-6 text-blue-600 " fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
 </svg>
 </div>
 </div>
 </div>
 </a>

 <!-- New Comments -->
 <a href="{{ route('dashboard.comments') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 hover:shadow-md transition-shadow cursor-pointer">
 <div class="flex items-center justify-between">
 <div>
 <p class="text-sm font-medium text-gray-500 ">New Comments</p>
 <p class="text-3xl font-bold text-gray-900 mt-2">{{ $stats['new_comments'] }}</p>
 </div>
 <div class="flex-shrink-0">
 <div class="h-12 w-12 rounded-full bg-purple-100 flex items-center justify-center">
 <svg class="h-6 w-6 text-purple-600 " fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
 </svg>
 </div>
 </div>
 </div>
 </a>
 </div>

 <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
 <!-- Recent Activity Feed -->
 <div class="lg:col-span-1 bg-white overflow-hidden shadow-sm sm:rounded-lg">
 <div class="px-6 py-4 border-b border-gray-200 ">
 <div class="flex items-center justify-between">
 <h3 class="text-lg font-semibold text-gray-900 ">Recent Activity</h3>
 <select id="project-filter" class="text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
 <option value="">All Projects</option>
 @foreach($projects as $project)
 <option value="{{ $project->id }}">{{ $project->name }}</option>
 @endforeach
 </select>
 </div>
 </div>
 <div class="p-6 max-h-96 overflow-y-auto">
 @if($recentActivities->count() > 0)
 <div class="space-y-4">
 @foreach($recentActivities as $activity)
 @if($activity->subject)
 <div class="flex items-start space-x-3 p-3 rounded-lg hover:bg-gray-50 :bg-gray-700 transition-colors activity-item" data-project-id="{{ $activity->subject->project_id ?? '' }}">
 <div class="flex-shrink-0">
 @php
 $subject = $activity->subject;
 $subjectType = get_class($subject);
 $bgColor = 'bg-gray-100 ';
 $textColor = 'text-gray-600 ';
 @endphp

 @if($subjectType === 'App\Models\Workspace')
 @php
 $bgColor = 'bg-blue-100 ';
 $textColor = 'text-blue-600 ';
 @endphp
 @elseif($subjectType === 'App\Models\Project')
 @php
 $bgColor = 'bg-green-100 ';
 $textColor = 'text-green-600 ';
 @endphp
 @elseif($subjectType === 'App\Models\Folder')
 @php
 $bgColor = 'bg-yellow-100 ';
 $textColor = 'text-yellow-600 ';
 @endphp
 @elseif($subjectType === 'App\Models\Asset')
 @php
 $bgColor = 'bg-purple-100 ';
 $textColor = 'text-purple-600 ';
 @endphp
 @endif

 <div class="h-8 w-8 rounded-full {{ $bgColor }} flex items-center justify-center">
 <svg class="h-4 w-4 {{ $textColor }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
 </svg>
 </div>
 </div>
 <div class="flex-1 min-w-0">
 <p class="text-sm text-gray-900 line-clamp-2">
 {{ $activity->description }}
 </p>
 <p class="text-xs text-gray-400 mt-1">
 {{ $activity->created_at->diffForHumans() }}
 </p>
 </div>
 </div>
 @endif
 @endforeach
 </div>
 @else
 <p class="text-gray-500 text-center py-8">
 No recent activity yet.
 </p>
 @endif
 </div>
 </div>

 <!-- Project Cards Grid -->
 <div class="lg:col-span-2">
 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
 @foreach($projects as $project)
 <a href="{{ route('workspace.show', [$project->workspace, $project]) }}" 
 class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow cursor-pointer">
 <!-- Thumbnail -->
 <div class="h-40 bg-gray-100 relative">
 @if($project->assets->count() > 0 && $project->assets->first()->isImage())
 <img src="{{ asset('storage/' . $project->assets->first()->file_path) }}" 
 alt="{{ $project->name }}" 
 class="w-full h-full object-cover">
 @else
 <div class="w-full h-full flex items-center justify-center">
 <svg class="h-16 w-16 text-gray-400 " fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
 </svg>
 </div>
 @endif

 <!-- Pending Approvals Badge -->
 @if($project->pending_approvals_count > 0)
 <div class="absolute top-3 right-3 bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded-full">
 {{ $project->pending_approvals_count }} pending
 </div>
 @endif
 </div>

 <!-- Project Info -->
 <div class="p-4">
 <h4 class="font-semibold text-gray-900 text-lg mb-1">{{ $project->name }}</h4>
 <p class="text-sm text-gray-500 mb-3">{{ $project->client_name ?? 'No client' }}</p>

 <div class="flex items-center justify-between text-sm">
 <div class="flex items-center space-x-4">
 @if($project->deadline)
 <span class="text-gray-500 ">
 <svg class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
 </svg>
 {{ $project->deadline->format('M d') }}
 </span>
 @endif
 <span class="px-2 py-1 text-xs font-medium rounded-full 
 {{ $project->status === 'active' ? 'bg-green-100 text-green-800 ' : 'bg-gray-100 text-gray-800 ' }}">
 {{ ucfirst($project->status) }}
 </span>
 </div>
 </div>
 </div>
 </a>
 @endforeach
 </div>

 @if($projects->count() === 0)
 <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-12 text-center">
 <svg class="h-16 w-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
 </svg>
 <h3 class="text-lg font-medium text-gray-900 mb-2">No projects yet</h3>
 <p class="text-gray-500 mb-4">Get started by creating your first project.</p>
 <a href="{{ route('workspaces.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
 Create Project
 </a>
 </div>
 @endif
 </div>
 </div>
 </div>
 </div>

 <script>
 document.addEventListener('DOMContentLoaded', function() {
 const projectFilter = document.getElementById('project-filter');
 const activityItems = document.querySelectorAll('.activity-item');

 projectFilter.addEventListener('change', function() {
 const selectedProjectId = this.value;

 activityItems.forEach(item => {
 const itemProjectId = item.getAttribute('data-project-id');
 if (selectedProjectId === '' || itemProjectId === selectedProjectId) {
 item.style.display = 'flex';
 } else {
 item.style.display = 'none';
 }
 });
 });
 });
 </script>
</x-app-layout>
