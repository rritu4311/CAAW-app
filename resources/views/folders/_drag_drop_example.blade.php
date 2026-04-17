{{-- 
    Drag-and-Drop Folder Ordering Example
    This file demonstrates how to implement drag-and-drop folder reordering using SortableJS
    
    Usage:
    1. Include SortableJS library in your layout or add it via CDN
    2. Include this partial in your folder view
    3. Pass the parent_id to the component
    
    Example: @include('folders._drag_drop_example', ['parentId' => $folder->parent_folder_id])
--}}

{{-- SortableJS Library (CDN) - Add this to your layout or include here --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>

<div id="folder-drag-drop-container" class="folder-drag-drop">
    {{-- Loading state --}}
    <div id="loading-state" class="text-center py-4">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    {{-- Folders list - will be populated via JavaScript --}}
    <ul id="folders-list" class="list-group mb-3" style="display: none;">
        {{-- Folder items will be dynamically inserted here --}}
    </ul>

    {{-- Error message --}}
    <div id="error-message" class="alert alert-danger" style="display: none;"></div>
</div>

<style>
    /* Drag-and-drop styles */
    .folder-item {
        cursor: move;
        background: #fff;
        border: 1px solid #dee2e6;
        padding: 12px 16px;
        margin-bottom: 8px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.2s ease;
    }

    .folder-item:hover {
        background: #f8f9fa;
        border-color: #adb5bd;
    }

    .folder-item.sortable-ghost {
        opacity: 0.4;
        background: #e9ecef;
    }

    .folder-item.sortable-drag {
        background: #fff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transform: scale(1.02);
    }

    .drag-handle {
        color: #6c757d;
        margin-right: 12px;
        cursor: grab;
    }

    .drag-handle:active {
        cursor: grabbing;
    }

    .folder-name {
        flex: 1;
        font-weight: 500;
    }

    .folder-actions {
        display: flex;
        gap: 8px;
    }

    .saving-indicator {
        display: none;
        color: #6c757d;
        font-size: 0.875rem;
    }

    .saving-indicator.active {
        display: inline-block;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Configuration
        const parentId = {{ isset($parentId) ? $parentId : 'null' }};
        const foldersList = document.getElementById('folders-list');
        const loadingState = document.getElementById('loading-state');
        const errorMessage = document.getElementById('error-message');

        // Initialize SortableJS
        let sortable = null;

        // Fetch folders from API
        async function fetchFolders() {
            try {
                loadingState.style.display = 'block';
                foldersList.style.display = 'none';
                errorMessage.style.display = 'none';

                const url = parentId 
                    ? `/folders/by-parent/${parentId}`
                    : '/folders/by-parent';

                const response = await fetch(url);
                
                if (!response.ok) {
                    throw new Error('Failed to fetch folders');
                }

                const data = await response.json();
                renderFolders(data.folders);

                // Initialize SortableJS after rendering
                initSortable();

            } catch (error) {
                console.error('Error fetching folders:', error);
                errorMessage.textContent = 'Error loading folders. Please try again.';
                errorMessage.style.display = 'block';
            } finally {
                loadingState.style.display = 'none';
            }
        }

        // Render folders in the list
        function renderFolders(folders) {
            foldersList.innerHTML = '';
            
            if (folders.length === 0) {
                foldersList.innerHTML = '<li class="list-group-item text-center text-muted">No folders found</li>';
            } else {
                folders.forEach(folder => {
                    const li = document.createElement('li');
                    li.className = 'folder-item';
                    li.setAttribute('data-id', folder.id);
                    
                    li.innerHTML = `
                        <div style="display: flex; align-items: center; flex: 1;">
                            <span class="drag-handle">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M7 2a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zM7 5a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm-3 3a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm-3 3a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm3 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                                </svg>
                            </span>
                            <span class="folder-name">${escapeHtml(folder.name)}</span>
                        </div>
                        <div class="folder-actions">
                            <span class="badge bg-secondary">Order: ${folder.order}</span>
                        </div>
                    `;
                    
                    foldersList.appendChild(li);
                });
            }

            foldersList.style.display = 'block';
        }

        // Initialize SortableJS
        function initSortable() {
            if (sortable) {
                sortable.destroy();
            }

            sortable = Sortable.create(foldersList, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                handle: '.drag-handle',
                onEnd: handleSortEnd
            });
        }

        // Handle sort end event
        async function handleSortEnd(evt) {
            const folderIds = [];
            const items = foldersList.querySelectorAll('.folder-item');
            
            items.forEach(item => {
                folderIds.push(parseInt(item.getAttribute('data-id')));
            });

            try {
                // Send new order to backend
                const response = await fetch('/folders/reorder', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        folder_ids: folderIds,
                        parent_id: parentId
                    })
                });

                if (!response.ok) {
                    throw new Error('Failed to update order');
                }

                const result = await response.json();
                
                if (result.success) {
                    console.log('Folders reordered successfully');
                    // Refresh the list to show updated order values
                    await fetchFolders();
                } else {
                    throw new Error(result.message || 'Failed to reorder');
                }

            } catch (error) {
                console.error('Error reordering folders:', error);
                alert('Failed to save new order. Please try again.');
                // Revert by refreshing the list
                await fetchFolders();
            }
        }

        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Initial fetch
        fetchFolders();
    });
</script>
