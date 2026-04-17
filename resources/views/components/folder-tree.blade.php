@props(['folders', 'level' => 0, 'projectId', 'readOnly' => false, 'parentId' => null])

@once
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
@endonce

<ul class="folder-list {{ $level > 0 ? 'ml-6 mt-2' : '' }} space-y-2" data-parent-id="{{ $parentId }}" data-project-id="{{ $projectId }}">
 @forelse($folders as $folder)
 <li class="folder-item flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:bg-gray-900 transition-colors cursor-move" data-folder-id="{{ $folder->id }}">
 <div class="flex items-center space-x-2 flex-1">
 @if(!$readOnly)
 <span class="drag-handle text-gray-400 hover:text-gray-600 cursor-grab">
 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
 </svg>
 </span>
 @endif
 <svg class="w-4 h-4 text-gray-400 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
 </svg>
 <a href="{{ route('folders.show', $folder->id) }}" class="font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:text-blue-400 hover:underline">
 {{ $folder->name }}
 </a>
 </div>

 @if(!$readOnly)
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
 @endif
 </li>

 {{-- Recursively render children if they exist --}}
 {{-- @if($folder->children && $folder->children->count() > 0)
 <x-folder-tree
 :folders="$folder->children"
 :level="$level + 1"
 :projectId="$projectId"
 :readOnly="$readOnly"
 :parentId="$folder->id"
 />
 @endif --}}
 @empty
 <li class="text-gray-500 dark:text-gray-500 text-center py-4">No folders yet. Create your first folder below!</li>
 @endforelse
</ul>

@once
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize SortableJS for all folder lists
    const folderLists = document.querySelectorAll('.folder-list');

    folderLists.forEach(function(list) {
        if (list.querySelector('.folder-item')) {
            new Sortable(list, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                handle: '.drag-handle',
                onEnd: async function(evt) {
                    const folderIds = [];
                    const items = list.querySelectorAll('.folder-item');

                    items.forEach(item => {
                        folderIds.push(parseInt(item.getAttribute('data-folder-id')));
                    });

                    const parentId = list.getAttribute('data-parent-id');
                    const projectId = list.getAttribute('data-project-id');

                    // Convert string "null" to actual null
                    const parentIdValue = (parentId === 'null' || parentId === '' || parentId === null) ? null : parseInt(parentId);

                    try {
                        console.log('Reordering folders:', { folderIds, parentId: parentIdValue });

                        const response = await fetch('/folders/reorder', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            },
                            body: JSON.stringify({
                                folder_ids: folderIds,
                                parent_id: parentIdValue
                            })
                        });

                        console.log('Response status:', response.status);
                        console.log('Response content type:', response.headers.get('content-type'));

                        if (!response.ok) {
                            const contentType = response.headers.get('content-type');
                            if (contentType && contentType.includes('application/json')) {
                                const errorData = await response.json();
                                console.error('Server error:', errorData);
                                throw new Error(errorData.error || 'Failed to update order');
                            } else {
                                const text = await response.text();
                                console.error('Non-JSON response:', text.substring(0, 200));
                                throw new Error('Server returned non-JSON response. Status: ' + response.status);
                            }
                        }

                        const result = await response.json();

                        if (result.success) {
                            console.log('Folders reordered successfully');
                        } else {
                            console.error('Reorder failed:', result);
                            throw new Error(result.message || 'Failed to reorder');
                        }

                    } catch (error) {
                        console.error('Error reordering folders:', error);
                        alert('Failed to save new order. Please try again.\n\nError: ' + error.message);
                        // Reload page to revert changes
                        window.location.reload();
                    }
                }
            });
        }
    });
});
</script>

<style>
    .folder-item.sortable-ghost {
        opacity: 0.4;
        background: #e9ecef;
    }

    .folder-item.sortable-drag {
        background: #fff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transform: scale(1.02);
    }

    .drag-handle:active {
        cursor: grabbing;
    }
</style>
@endonce
