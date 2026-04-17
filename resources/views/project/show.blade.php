<x-app-layout>
 <x-slot name="header">
 <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
 {{ __('Project') }}
 </h2>
 </x-slot>

 <div class="py-12">
 <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
 <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
 <div class="p-6 bg-white dark:bg-gray-800 ">

 <!-- Back Button -->
 <div class="mb-4">
 <a href="{{ route('workspaces.show', $project->workspace) }}"
 class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:text-gray-100 :text-gray-200 transition-colors">
 <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
 </svg>
 Back to {{ $project->workspace->name }}
 </a>
 </div>

 @if(session('error'))
 <div class="mb-4 p-4 bg-red-100 dark:bg-red-900 border border-red-400 text-red-700 rounded">
 {{ session('error') }}
 </div>
 @endif

 @if($errors->any())
 <div class="mb-4 p-4 bg-red-100 dark:bg-red-900 border border-red-400 text-red-700 rounded">
 <ul>
 @foreach($errors->all() as $error)
 <li>{{ $error }}</li>
 @endforeach
 </ul>
 </div>
 @endif

 <div class="flex justify-between items-center mb-6">
 <div>
 <div class="flex items-center gap-3 mb-4">
 <h1 class="text-3xl font-bold">{{ $project->name }}</h1>
 @if($project->status === 'archived')
 <span class="inline-flex items-center px-3 py-1 bg-red-100 dark:bg-red-900 text-red-700 rounded-full text-sm font-medium" title="Archived">
 <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
 </svg>
 Archived
 </span>
 @endif
 @if(isset($readOnly) && $readOnly)
 <span class="px-3 py-1 bg-yellow-100 dark:bg-yellow-900 text-yellow-800 rounded-full text-sm font-medium">Read-Only (Workspace Admin)</span>
 @endif
 </div>
 </div>
 
 <!-- Owner Action Buttons -->
 @if(($project->isOwnedBy(auth()->user()) || auth()->user()->isWorkspaceOwner($project) || auth()->user()->hasWorkspaceRole($project->workspace, ['user'])) && !$readOnly)
 <div class="flex gap-2">
 <!-- Share Button -->
 <a href="{{ route('projects.share', $project) }}"
 class="p-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors" title="Share Project">
 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"/>
 </svg>
 </a>

 <!-- Create Workflow Button -->
 <a href="{{ route('workflows.create', $project) }}"
 class="px-2 py-1 text-xs bg-green-400 text-black rounded hover:bg-green-500 inline-flex items-center gap-1"
 title="Create Workflow">
 
 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
 d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
 </svg>

 <span>Workflow</span>
 </a>

 <!-- Create Folder Button -->
 <button onclick="openModal('createFolderModalroot')"
 class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors" title="Create Folder">
 Create Folder
 </button>

 <!-- Delete Project Icon -->
 <form action="{{ route('workspace.destroy', [$project->workspace, $project]) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this project?')">
 @csrf
 @method('DELETE')
 <button type="submit" class="p-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors" title="Delete Project">
 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
 </svg>
 </button>
 </form>
 </div>
 @endif

 <!-- Admin Action Buttons -->
 @if(auth()->user()->isWorkspaceAdmin($project) && !auth()->user()->isWorkspaceOwner($project))
 <div class="flex gap-2">
 <!-- Archive/Unarchive Button -->
 @if($project->status === 'archived')
 <form method="POST" action="{{ route('projects.unarchive', $project) }}" class="inline" onsubmit="return confirm('Are you sure you want to unarchive this project?')">
 @csrf
 @method('PATCH')
 <button type="submit"
 class="bg-green-600 text-white p-2 rounded hover:bg-green-700 transition"
 title="Unarchive Project">
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
 class="bg-red-600 text-white p-2 rounded hover:bg-red-700 transition"
 title="Archive Project">
 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
 </svg>
 </button>
 </form>
 @endif
 </div>
 @endif
 </div>

 <!-- Project Details -->
 <div class="bg-gray-50 dark:bg-gray-700 shadow rounded-lg p-6 space-y-4 mb-6">
 <p><strong>Client:</strong> {{ $project->client_name }}</p>
 <p><strong>Description:</strong> {{ $project->description }}</p>
 <p><strong>Deadline:</strong> {{ $project->deadline }}</p>
 <p><strong>Created At:</strong> {{ $project->created_at }}</p>
 </div>

 <!-- Workflow Management Section -->
 <div class="bg-white dark:bg-gray-800 border rounded-lg p-6 mb-6">
 <div class="flex items-center justify-between mb-4">
 <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Approval Workflows</h2>
 <!-- <a href="{{ route('workflows.create', $project) }}"
 class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 transition-colors text-sm">
 + Create Workflow
 </a> -->
 </div>
 
 @if($project->workflows->count() > 0)
 <div class="space-y-3">
 @foreach($project->workflows as $workflow)
 <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
 <div class="flex items-center gap-3">
 <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
 <svg class="w-5 h-5 text-purple-600 " fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
 </svg>
 </div>
 <div>
 <div class="font-medium text-gray-900 dark:text-gray-100 ">{{ $workflow->name }}</div>
 <div class="text-xs text-gray-500 dark:text-gray-500 ">
 {{ ucfirst($workflow->type) }} • {{ $workflow->getTotalSteps() }} step(s)
 @if($workflow->deadline_hours)
 • {{ $workflow->deadline_hours }}h deadline
 @endif
 </div>
 </div>
 </div>
 <div class="flex items-center gap-2">
 @if($workflow->is_active)
 <span class="px-2 py-1 text-xs bg-green-100 dark:bg-green-900 text-green-700 rounded-full">Active</span>
 @else
 <span class="px-2 py-1 text-xs bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-300 rounded-full">Inactive</span>
 @endif
 <a href="{{ route('workflows.edit', $workflow) }}" class="p-1 text-blue-600 dark:text-blue-400 hover:text-blue-700 transition-colors" title="Edit Workflow">
 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
 </svg>
 </a>
 <form action="{{ route('workflows.destroy', $workflow) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete the workflow \"' + {{ json_encode($workflow->name) }} + '\"? This action cannot be undone.')">
 @csrf
 @method('DELETE')
 <button type="submit" class="p-1 text-red-600 dark:text-red-400 hover:text-red-700 transition-colors">
 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
 </svg>
 </button>
 </form>
 </div>
 </div>
 @endforeach
 </div>
 @else
 <div class="text-center py-8 text-gray-500 dark:text-gray-500 ">
 <p>No workflows configured yet.</p>
 <a href="{{ route('workflows.create', $project) }}" class="text-purple-600 hover:text-purple-700">Create your first workflow</a>
 </div>
 @endif
 </div>

 <!-- Folder Tree Section -->
 <div class="bg-white dark:bg-gray-800 border rounded-lg p-6">
 <h2 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-200">Project Folders</h2>
 
 @if(session('success'))
 <div class="bg-green-100 dark:bg-green-900 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
 {{ session('success') }}
 </div>
 @endif

 <div class="folder-tree">
 <x-folder-tree :folders="$folders" :projectId="$project->id" :readOnly="isset($readOnly) ? $readOnly : false" />
 </div>
 </div>

 </div>
 </div>
 </div>
 </div>

 <!-- Create Folder Modal -->
 <x-create-folder-modal :projectId="$project->id" :error="session('error')" />
 
 <!-- Edit Folder Modal -->
 <x-edit-folder-modal />

</x-app-layout>