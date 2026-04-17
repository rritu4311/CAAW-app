@props(['project', 'workspaceId'])
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 ease-in-out overflow-hidden border border-gray-200 dark:border-gray-700 group">
 <!-- Card Header with Status Badge -->
 <div class="relative bg-gradient-to-r from-blue-500 to-purple-600 p-6 text-white">
 <div class="absolute top-4 right-4">
 <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white dark:bg-gray-800/20 backdrop-blur-sm border border-white/30">
 @if(now()->gt(\Carbon\Carbon::parse($project->deadline)))
 <span class="w-2 h-2 bg-red-400 rounded-full mr-2 animate-pulse"></span>
 Overdue
 @elseif(now()->diffInDays(\Carbon\Carbon::parse($project->deadline)) <= 3)
 <span class="w-2 h-2 bg-yellow-400 rounded-full mr-2 animate-pulse"></span>
 Due Soon
 @else
 <span class="w-2 h-2 bg-green-400 rounded-full mr-2"></span>
 On Track
 @endif
 </span>
 </div>
 
 <h3 class="text-2xl font-bold mb-2 pr-20">{{ $project->name }}</h3>
 <div class="flex items-center space-x-4 text-blue-100">
 <div class="flex items-center">
 <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
 <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
 </svg>
 {{ $project->client_name }}
 </div>
 <div class="flex items-center">
 <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
 <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
 </svg>
 {{ \Carbon\Carbon::parse($project->deadline)->format('M d, Y') }}
 </div>
 </div>
 </div>

 <!-- Card Body -->
 <div class="p-6">
 <!-- Description -->
 <div class="mb-6">
 <h4 class="text-sm font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-wider mb-2">Description</h4>
 <p class="text-gray-700 dark:text-gray-300 leading-relaxed">{{ Str::limit($project->description, 150) }}</p>
 </div>

 <!-- Progress Bar -->
 <div class="mb-6">
 <div class="flex justify-between items-center mb-2">
 <h4 class="text-sm font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-wider">Progress</h4>
 <span class="text-sm font-medium text-blue-600 dark:text-blue-400 ">{{ rand(40, 90) }}%</span>
 </div>
 <div class="w-full bg-gray-200 rounded-full h-2">
 <div class="bg-gradient-to-r from-blue-500 to-purple-600 h-2 rounded-full transition-all duration-500" style="width: {{ rand(40, 90) }}%"></div>
 </div>
 </div>

 <!-- Quick Stats -->
 <div class="grid grid-cols-3 gap-4 mb-6">
 <div class="text-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
 <div class="text-2xl font-bold text-blue-600 dark:text-blue-400 ">{{ rand(5, 25) }}</div>
 <div class="text-xs text-gray-500 dark:text-gray-500 ">Tasks</div>
 </div>
 <div class="text-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
 <div class="text-2xl font-bold text-green-600 dark:text-green-400 ">{{ rand(3, 15) }}</div>
 <div class="text-xs text-gray-500 dark:text-gray-500 ">Completed</div>
 </div>
 <div class="text-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
 <div class="text-2xl font-bold text-orange-600 ">{{ rand(1, 7) }}</div>
 <div class="text-xs text-gray-500 dark:text-gray-500 ">Pending</div>
 </div>
 </div>

 <!-- Team Members (Mock) -->
 <div class="mb-6">
 <h4 class="text-sm font-semibold text-gray-500 dark:text-gray-500 uppercase tracking-wider mb-3">Team Members</h4>
 <div class="flex -space-x-2">
 @for($i = 1; $i <= rand(2, 4); $i++)
 <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-purple-600 border-2 border-white flex items-center justify-center text-white text-xs font-semibold">
 {{ strtoupper(chr(64 + $i)) }}
 </div>
 @endfor
 <div class="w-8 h-8 rounded-full bg-gray-300 border-2 border-white flex items-center justify-center text-gray-600 dark:text-gray-400 text-xs font-semibold">
 +{{ rand(1, 3) }}
 </div>
 </div>
 </div>

 <!-- Action Buttons -->
 <div class="flex space-x-3">
 <button onclick="window.location.href='/workspaces/{{ $workspaceId }}/projects/{{ $project->id }}'" 
 class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-4 rounded-lg transition duration-200 flex items-center justify-center group-hover:scale-105">
 <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
 </svg>
 View Details
 </button>
 
 <!-- Admin Archive Form -->
 @if(auth()->user()->isWorkspaceAdmin(\App\Models\Workspace::find($workspaceId)) && !auth()->user()->isWorkspaceOwner(\App\Models\Workspace::find($workspaceId)))
 @if($project->status !== 'archived')
 <form method="POST" action="{{ route('projects.archive', $project) }}" class="inline" onsubmit="return confirm('Are you sure you want to archive this project?')">
 @csrf
 @method('PATCH')
 <button type="submit" 
 class="bg-red-600 hover:bg-red-700 text-white font-medium py-2.5 px-4 rounded-lg transition duration-200 flex items-center"
 title="Archive Project">
 <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
 </svg>
 Archive
 </button>
 </form>
 @endif
 @endif
 <!-- Admin Archive/Unarchive Button -->
 @if(auth()->user()->isWorkspaceAdmin(\App\Models\Workspace::find($workspaceId)) && !auth()->user()->isWorkspaceOwner(\App\Models\Workspace::find($workspaceId)))
 @if($project->status === 'archived')
 <form method="POST" action="{{ route('projects.unarchive', $project) }}" class="inline" onsubmit="return confirm('Are you sure you want to unarchive this project?')">
 @csrf
 @method('PATCH')
 <button type="submit" 
 class="bg-green-100 dark:bg-green-900 hover:bg-green-200 :bg-green-800 text-green-700 font-medium py-2.5 px-4 rounded-lg transition duration-200">
 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004 12v1m4.21-12l3 3m-3-3l-3 3m2.9 13.9a8.001 8.001 0 0011.319 0l1.414 1.414A10.001 10.001 0 0112 21c-3.217 0-6.32-1.28-8.54-3.52l1.42-1.42z"/>
 </svg>
 </button>
 </form>
 @else
 <form method="POST" action="{{ route('projects.archive', $project) }}" class="inline" onsubmit="return confirm('Are you sure you want to archive this project?')">
 @csrf
 @method('PATCH')
 <button type="submit" 
 class="bg-orange-100 hover:bg-orange-200 :bg-orange-800 text-orange-700 font-medium py-2.5 px-4 rounded-lg transition duration-200">
 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
 </svg>
 </button>
 </form>
 @endif
 @endif
 
 <!-- Owner Edit/Delete Buttons -->
 @if($project->isOwnedBy(auth()->user()) || auth()->user()->isWorkspaceOwner(\App\Models\Workspace::find($workspaceId)))
 <button onclick="editProject({{ $project->id }}, '{{ $project->name }}', '{{ $project->client_name }}', '{{ $project->description }}', '{{ $project->deadline }}')" 
 class="bg-gray-100 dark:bg-gray-900 hover:bg-gray-200 :bg-gray-600 text-gray-700 dark:text-gray-300 font-medium py-2.5 px-4 rounded-lg transition duration-200">
 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
 </svg>
 </button>
 <button onclick="deleteProject({{ $project->id }})" 
 class="bg-red-100 dark:bg-red-900 hover:bg-red-200 :bg-red-800 text-red-700 font-medium py-2.5 px-4 rounded-lg transition duration-200">
 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
 </svg>
 </button>
 @endif
 </div>
 </div>

 <!-- Card Footer -->
 <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 border-t border-gray-200 dark:border-gray-700 ">
 <div class="flex justify-between items-center text-sm">
 <div class="text-gray-500 dark:text-gray-500 ">
 Created {{ $project->created_at->diffForHumans() }}
 </div>
 <div class="flex space-x-4">
 <button class="text-blue-600 dark:text-blue-400 hover:underline flex items-center">
 <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
 <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"/>
 <path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z"/>
 </svg>
 {{ rand(3, 12) }}
 </button>
 <button class="text-gray-600 dark:text-gray-400 hover:underline flex items-center">
 <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
 <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
 </svg>
 {{ rand(8, 24) }}
 </button>
 </div>
 </div>
 </div>
</div>
