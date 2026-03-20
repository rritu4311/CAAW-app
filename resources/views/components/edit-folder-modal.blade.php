<!-- Edit Folder Modal -->
<div id="editFolderModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <!-- Modal Content -->
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Edit Folder</h3>
                <button onclick="closeModal('editFolderModal')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="editFolderForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="folder_id" id="editFolderId">
                
                <div class="mb-4">
                    <label for="editFolderName" class="block text-sm font-medium text-gray-700 mb-2">
                        Folder Name
                    </label>
                    <input type="text" 
                           id="editFolderName" 
                           name="name" 
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Enter folder name">
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            onclick="closeModal('editFolderModal')"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                        Update Folder
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript for Edit Modal -->
<script>
function openEditFolderModal(folderId, folderName) {
    document.getElementById('editFolderId').value = folderId;
    document.getElementById('editFolderName').value = folderName;
    
    // Set the form action dynamically
    const form = document.getElementById('editFolderForm');
    form.action = `/folders/${folderId}`;
    
    openModal('editFolderModal');
}
</script>
