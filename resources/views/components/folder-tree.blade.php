@props(['folders', 'level' => 0, 'projectId'])

<ul class="{{ $level > 0 ? 'ml-6 mt-2' : '' }} space-y-2">
    @forelse($folders as $folder)
        <li class="flex items-center justify-between p-2 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
            <div class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                </svg>
                <a href="{{ route('folders.show', $folder->id) }}" class="font-medium text-gray-700 hover:text-blue-600 hover:underline">
                    {{ $folder->name }}
                </a>
            </div>
            
            <div class="flex items-center space-x-2">
                <!-- Edit Button -->
                <button onclick="openEditFolderModal({{ $folder->id }}, '{{ $folder->name }}')" 
                        class="p-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700" 
                        title="Edit Folder">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </button>
                
                <!-- Delete Button -->
                <form action="{{ route('folders.destroy', $folder->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this folder and all its contents?')" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-1 text-xs bg-red-600 text-white rounded hover:bg-red-700" 
                            title="Delete Folder">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </li>
    @empty
        <li class="text-gray-500 text-center py-4">No folders yet. Create your first folder below!</li>
    @endforelse
</ul>
