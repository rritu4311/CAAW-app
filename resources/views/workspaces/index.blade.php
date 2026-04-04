<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Workspaces') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white dark:bg-gray-800">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                            My Workspaces
                        </h1>
                        <a href="{{ route('workspaces.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                            Create Workspace
                        </a>
                    </div>

                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="space-y-3">
                        @if($workspaces->count() === 0)
                            <div class="col-span-full text-center py-12">
                                <p class="text-gray-500 text-lg">
                                    No workspaces found. Create your first workspace!
                                </p>
                            </div>
                        @else
                            <div class="space-y-3">
                            @foreach($workspaces as $workspace)
                                @php
                                    $isWorkspaceAdmin = \App\Models\WorkspaceUser::where('workspace_id', $workspace->id)
                                        ->where('user_id', auth()->id())
                                        ->where('status', 'approved')
                                        ->where('role', 'admin')
                                        ->exists();
                                @endphp
                                <div class="flex items-center justify-between p-4 bg-white dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 hover:shadow-md transition-shadow cursor-pointer" onclick="window.location='{{ route('workspace.page', $workspace) }}'">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                {{ $workspace->name }}
                                            </h3>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Created: {{ $workspace->created_at->format('M d, Y') }}
                                                @if(auth()->user()->id !== $workspace->owner_id)
                                                    | Owner: {{ $workspace->owner->name }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2" onclick="event.stopPropagation()">
                                    @if($workspace->isOwnedBy(auth()->user()) || $isWorkspaceAdmin)
                                        <a href="{{ route('workspaces.share', $workspace) }}" 
                                           class="bg-blue-600 text-white p-2 rounded hover:bg-blue-700 transition"
                                           title="Share Workspace">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"/>
                                            </svg>
                                        </a>
                                    @endif
                                        @if($workspace->isOwnedBy(auth()->user()))
                                            <a href="{{ route('workspaces.edit', $workspace) }}" 
                                               class="bg-blue-600 text-white p-2 rounded hover:bg-blue-700 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                        @endif
                                        @if($workspace->isOwnedBy(auth()->user()))
                                            <form action="{{ route('workspaces.destroy', $workspace) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this workspace?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="bg-red-600 text-white p-2 rounded hover:bg-red-700 transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                            <button type="button" 
                                                    class="bg-red-600 text-white p-2 rounded hover:bg-red-700 transition"
                                                    title="Archive Workspace">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
