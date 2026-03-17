<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">File Manager</h1>
                            <p class="text-gray-600 dark:text-gray-400 mt-1">Upload and organize your files</p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <button onclick="createFolder()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                New Folder
                            </button>
                        </div>
                    </div>

                    <!-- Breadcrumb Navigation -->
                    <div id="breadcrumb" class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400 mb-4">
                        <a href="#" onclick="navigateToFolder('root')" class="hover:text-blue-600 dark:hover:text-blue-400">Home</a>
                    </div>

                    <!-- Upload Area -->
                    <div id="uploadArea" class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center hover:border-blue-500 dark:hover:border-blue-400 transition-colors mb-6">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <p class="text-lg font-medium text-gray-900 dark:text-white mb-2">Drag and drop your files here</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">or click to browse</p>
                        <input type="file" id="fileInput" multiple class="hidden" accept=".jpg,.jpeg,.png,.gif,.webp,.mp4,.mov,.webm,.pdf,.docx,.xlsx,.txt,.md">
                        <button onclick="document.getElementById('fileInput').click()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            Choose Files
                        </button>
                        <div class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                            <p>Supported: Images (PNG, JPG, GIF, WebP) max 50MB each</p>
                            <p>Videos (MP4, MOV, WebM) max 500MB each</p>
                            <p>Documents (PDF, DOCX, XLSX) max 50MB each</p>
                            <p>Text files (TXT, MARKDOWN) max 50MB each</p>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div id="progressContainer" class="hidden mb-6">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Uploading...</span>
                            <span id="progressText" class="text-sm text-gray-500 dark:text-gray-400">0%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div id="progressBar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                    </div>

                    <!-- File Grid -->
                    <div id="fileGrid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        <!-- Files will be loaded here -->
                    </div>

                    <!-- Empty State -->
                    <div id="emptyState" class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-gray-500 dark:text-gray-400">No files in this folder</p>
                        <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Upload some files to get started</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Folder Modal -->
    <div id="folderModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Create New Folder</h3>
                <input type="text" id="folderName" placeholder="Folder name" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                <div class="mt-4 flex justify-end space-x-3">
                    <button onclick="closeFolderModal()" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500 transition-colors">
                        Cancel
                    </button>
                    <button onclick="confirmCreateFolder()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Create
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentFolder = 'root';
        let uploadedFiles = [];

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadFiles();
            setupDragAndDrop();
            setupFileInput();
        });

        function setupDragAndDrop() {
            const uploadArea = document.getElementById('uploadArea');
            
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                uploadArea.addEventListener(eventName, () => {
                    uploadArea.classList.add('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
                });
            });

            ['dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, () => {
                    uploadArea.classList.remove('border-blue-500', 'bg-blue-50', 'dark:bg-blue-900/20');
                });
            });

            uploadArea.addEventListener('drop', handleDrop);
        }

        function setupFileInput() {
            const fileInput = document.getElementById('fileInput');
            fileInput.addEventListener('change', handleFileSelect);
        }

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles(files);
        }

        function handleFileSelect(e) {
            const files = e.target.files;
            handleFiles(files);
        }

        function handleFiles(files) {
            uploadFiles(files);
        }

        function uploadFiles(files) {
            const formData = new FormData();
            const validFiles = [];
            
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                const validation = validateFile(file);
                
                if (validation.valid) {
                    formData.append('files[]', file);
                    validFiles.push(file);
                } else {
                    showNotification(validation.error, 'error');
                }
            }

            if (validFiles.length === 0) {
                return;
            }

            formData.append('folder', currentFolder);

            // Show progress
            showProgress();

            const xhr = new XMLHttpRequest();
            
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentComplete = Math.round((e.loaded / e.total) * 100);
                    updateProgress(percentComplete);
                }
            });

            xhr.addEventListener('load', function() {
                hideProgress();
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        showNotification(`Successfully uploaded ${response.uploaded_count} files`, 'success');
                        loadFiles();
                    } else {
                        showNotification('Upload failed', 'error');
                        if (response.errors) {
                            Object.values(response.errors).forEach(error => {
                                showNotification(error, 'error');
                            });
                        }
                    }
                } else {
                    showNotification('Upload failed', 'error');
                }
            });

            xhr.addEventListener('error', function() {
                hideProgress();
                showNotification('Upload failed', 'error');
            });

            xhr.open('POST', '/files/upload');
            xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            xhr.send(formData);
        }

        function validateFile(file) {
            const allowedTypes = [
                'image/jpeg', 'image/png', 'image/gif', 'image/webp',
                'video/mp4', 'video/mov', 'video/webm',
                'application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'text/plain', 'text/markdown'
            ];

            const maxSizeImage = 50 * 1024 * 1024; // 50MB
            const maxSizeVideo = 500 * 1024 * 1024; // 500MB
            const maxSizeOther = 50 * 1024 * 1024; // 50MB

            if (!allowedTypes.includes(file.type)) {
                return { valid: false, error: `File type ${file.type} not allowed` };
            }

            let maxSize;
            if (file.type.startsWith('image/')) {
                maxSize = maxSizeImage;
            } else if (file.type.startsWith('video/')) {
                maxSize = maxSizeVideo;
            } else {
                maxSize = maxSizeOther;
            }

            if (file.size > maxSize) {
                const maxSizeMB = maxSize / (1024 * 1024);
                return { valid: false, error: `File size exceeds ${maxSizeMB}MB limit` };
            }

            return { valid: true };
        }

        function showProgress() {
            document.getElementById('progressContainer').classList.remove('hidden');
            document.getElementById('uploadArea').classList.add('opacity-50');
        }

        function hideProgress() {
            document.getElementById('progressContainer').classList.add('hidden');
            document.getElementById('uploadArea').classList.remove('opacity-50');
            updateProgress(0);
        }

        function updateProgress(percent) {
            document.getElementById('progressBar').style.width = percent + '%';
            document.getElementById('progressText').textContent = percent + '%';
        }

        function loadFiles() {
            fetch(`/files?folder=${encodeURIComponent(currentFolder)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderFiles(data.files, data.folders);
                        updateBreadcrumb(currentFolder);
                    } else {
                        showNotification('Failed to load files', 'error');
                    }
                })
                .catch(error => {
                    showNotification('Failed to load files', 'error');
                });
        }

        function renderFiles(files, folders) {
            const fileGrid = document.getElementById('fileGrid');
            const emptyState = document.getElementById('emptyState');

            fileGrid.innerHTML = '';

            // Render folders first
            folders.forEach(folder => {
                const folderElement = createFolderElement(folder);
                fileGrid.appendChild(folderElement);
            });

            // Render files
            files.forEach(file => {
                const fileElement = createFileElement(file);
                fileGrid.appendChild(fileElement);
            });

            // Show/hide empty state
            if (files.length === 0 && folders.length === 0) {
                emptyState.classList.remove('hidden');
                fileGrid.classList.add('hidden');
            } else {
                emptyState.classList.add('hidden');
                fileGrid.classList.remove('hidden');
            }
        }

        function createFolderElement(folder) {
            const div = document.createElement('div');
            div.className = 'bg-gray-50 dark:bg-gray-700 rounded-lg p-4 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors cursor-pointer';
            div.onclick = () => navigateToFolder(folder.path);
            
            div.innerHTML = `
                <div class="flex flex-col items-center text-center">
                    <svg class="w-12 h-12 text-blue-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                    </svg>
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">${folder.name}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Folder</p>
                </div>
            `;
            
            return div;
        }

        function createFileElement(file) {
            const div = document.createElement('div');
            div.className = 'bg-gray-50 dark:bg-gray-700 rounded-lg p-4 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors';
            
            let icon = '';
            if (file.is_image) {
                icon = `<img src="${file.url}" alt="${file.original_name}" class="w-full h-24 object-cover rounded mb-2">`;
            } else if (file.is_video) {
                icon = `<div class="w-full h-24 bg-gray-200 dark:bg-gray-600 rounded mb-2 flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                </div>`;
            } else if (file.is_document) {
                icon = `<div class="w-full h-24 bg-gray-200 dark:bg-gray-600 rounded mb-2 flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>`;
            } else {
                icon = `<div class="w-full h-24 bg-gray-200 dark:bg-gray-600 rounded mb-2 flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>`;
            }
            
            div.innerHTML = `
                ${icon}
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate" title="${file.original_name}">${file.original_name}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">${file.formatted_size}</p>
                    <div class="mt-2 flex justify-center space-x-2">
                        ${file.is_image ? `<a href="${file.url}" target="_blank" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">View</a>` : ''}
                        <a href="${file.url}" download class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300">Download</a>
                        <button onclick="deleteFile('${file.path}')" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                    </div>
                </div>
            `;
            
            return div;
        }

        function navigateToFolder(folderPath) {
            currentFolder = folderPath;
            loadFiles();
        }

        function updateBreadcrumb(folder) {
            const breadcrumb = document.getElementById('breadcrumb');
            const parts = folder === 'root' ? [] : folder.replace('uploads/', '').split('/');
            
            let html = '<a href="#" onclick="navigateToFolder(\'root\')" class="hover:text-blue-600 dark:hover:text-blue-400">Home</a>';
            
            let currentPath = '';
            parts.forEach((part, index) => {
                currentPath += (currentPath ? '/' : '') + part;
                const folderPath = 'uploads/' + currentPath;
                html += ' / <a href="#" onclick="navigateToFolder(\'' + folderPath + '\')" class="hover:text-blue-600 dark:hover:text-blue-400">' + part + '</a>';
            });
            
            breadcrumb.innerHTML = html;
        }

        function createFolder() {
            document.getElementById('folderModal').classList.remove('hidden');
            document.getElementById('folderName').value = '';
            document.getElementById('folderName').focus();
        }

        function closeFolderModal() {
            document.getElementById('folderModal').classList.add('hidden');
        }

        function confirmCreateFolder() {
            const folderName = document.getElementById('folderName').value.trim();
            
            if (!folderName) {
                showNotification('Please enter a folder name', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('name', folderName);
            formData.append('parent_folder', currentFolder);

            fetch('/folders/create', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Folder created successfully', 'success');
                    closeFolderModal();
                    loadFiles();
                } else {
                    showNotification(data.error || 'Failed to create folder', 'error');
                }
            })
            .catch(error => {
                showNotification('Failed to create folder', 'error');
            });
        }

        function deleteFile(filePath) {
            if (!confirm('Are you sure you want to delete this file?')) {
                return;
            }

            const formData = new FormData();
            formData.append('path', filePath);

            fetch('/files/delete', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('File deleted successfully', 'success');
                    loadFiles();
                } else {
                    showNotification(data.error || 'Failed to delete file', 'error');
                }
            })
            .catch(error => {
                showNotification('Failed to delete file', 'error');
            });
        }

        function showNotification(message, type = 'info') {
            // Simple notification system
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-lg text-white z-50 ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 'bg-blue-500'
            }`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Handle Enter key in folder modal
        document.getElementById('folderName').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                confirmCreateFolder();
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeFolderModal();
            }
        });
    </script>
</x-app-layout>
