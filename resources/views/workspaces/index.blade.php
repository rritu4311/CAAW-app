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

                    <div class="space-y-6">
                        @if($workspaces->count() === 0)
                            <div class="col-span-full text-center py-12">
                                <p class="text-gray-500 text-lg">
                                    No workspaces found. Create your first workspace!
                                </p>
                            </div>
                        @else
                            @foreach($workspaces as $workspace)
                                <div onclick="window.location.href='{{ route('workspace.page', $workspace) }}'"
                                     class="group cursor-pointer bg-white dark:bg-gray-700 rounded-lg shadow-md hover:shadow-xl transition-all duration-300 ease-in-out p-6 border border-gray-200 dark:border-gray-600">
                                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between mb-3">
                                                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 group-hover:text-blue-600 transition">
                                                    {{ $workspace->name }}
                                                </h3>
                                                <div class="flex space-x-2">
                                                    <form action="{{ route('workspaces.edit', $workspace) }}" method="GET" class="inline">
                                                        <button type="submit" 
                                                                class="bg-blue-600 text-white p-2 rounded hover:bg-blue-700 transition"
                                                                onclick="event.stopPropagation()">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('workspaces.destroy', $workspace) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this workspace?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="bg-red-600 text-white p-2 rounded hover:bg-red-700 transition"
                                                                onclick="event.stopPropagation()">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Created: {{ $workspace->created_at->format('M d, Y') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
