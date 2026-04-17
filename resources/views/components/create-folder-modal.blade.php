@props(['projectId', 'parentFolderId' => null, 'error' => null])

<!-- Modal -->
<div id="createFolderModal{{ $parentFolderId ?: 'root' }}" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
 <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
 <!-- Modal Content -->
 <div class="mt-3">
 <div class="flex items-center justify-between mb-4">
 <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Create New Folder</h3>
 <button onclick="closeModal('createFolderModal{{ $parentFolderId ?: 'root' }}')" class="text-gray-400 dark:text-gray-400 hover:text-gray-600 dark:text-gray-400">
 <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
 </svg>
 </button>
 </div>

 <form action="{{ route('folders.store') }}" method="POST">
 @csrf
 <input type="hidden" name="project_id" value="{{ $projectId }}">
 @if($parentFolderId)
 <input type="hidden" name="parent_folder_id" value="{{ $parentFolderId }}">
 @endif

 @if($error)
 <div class="mb-4 p-3 bg-red-100 dark:bg-red-900 border border-red-400 text-red-700 rounded">
 <p>{{ $error }}</p>
 </div>
 @endif

 <div class="mb-4">
 <label for="folderName{{ $parentFolderId ?: 'root' }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
 Folder Name
 </label>
 <input type="text"
 id="folderName{{ $parentFolderId ?: 'root' }}"
 name="name"
 value="{{ old('name') }}"
 required
 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
 placeholder="Enter folder name">
 </div>
 
 <div class="flex justify-end space-x-3">
 <button type="button" 
 onclick="closeModal('createFolderModal{{ $parentFolderId ?: 'root' }}')"
 class="px-4 py-2 bg-gray-300 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-400 transition-colors">
 Cancel
 </button>
 <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
 Create Folder
 </button>
 </div>
 </form>
 </div>
 </div>
</div>

<!-- JavaScript for Modal -->
<script>
function openModal(modalId) {
 document.getElementById(modalId).classList.remove('hidden');
}

function closeModal(modalId) {
 document.getElementById(modalId).classList.add('hidden');
 // Clear the input field
 const input = document.querySelector('#' + modalId + ' input[name="name"]');
 if (input) {
 input.value = '';
 }
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
 if (event.target.classList.contains('bg-opacity-50')) {
 event.target.classList.add('hidden');
 }
});

// Auto-open modal if there are errors
document.addEventListener('DOMContentLoaded', function() {
 @if($error)
 openModal('createFolderModal{{ $parentFolderId ?: 'root' }}');
 @endif
});
</script>
