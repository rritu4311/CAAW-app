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
                <!-- Delete Button -->
                <form action="{{ route('folders.destroy', $folder->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this folder and all its contents?')" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700">
                        Delete
                    </button>
                </form>
            </div>
        </li>
    @empty
        <li class="text-gray-500 text-center py-4">No folders yet. Create your first folder below!</li>
    @endforelse
</ul>
