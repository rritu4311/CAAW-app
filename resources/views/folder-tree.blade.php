<ul class="folder-tree list-unstyled">
 @foreach($folders as $folder)
 <li class="folder-item">
 <div class="folder-node d-flex align-items-center py-1" onclick="toggleFolder(this, {{ $folder->id }})">
 <span class="folder-toggle me-2">
 @if($folder->children->count())
 <i class="fas fa-chevron-right text-muted"></i>
 @else
 <i class="fas fa-folder text-warning"></i>
 @endif
 </span>
 
 <span class="folder-content">
 <i class="fas fa-folder text-warning me-2"></i>
 <span class="folder-name">{{ $folder->name }}</span>
 
 <div class="folder-actions ms-auto">
 <button 
 class="btn btn-sm btn-outline-danger"
 title="Delete folder"
 onclick="event.stopPropagation()">
 <i class="fas fa-trash"></i>
 </button>
 </div>
 </span>
 </div>
 
 @if($folder->children->count())
 <ul class="folder-children ms-4 list-unstyled" style="display: none;">
 @include('folder-tree',['folders'=>$folder->children])
 </ul>
 @endif
 
 <!-- Folder Content Area (shown when folder is opened) -->
 <div class="folder-content-area ms-4" style="display: none;" id="folder-content-{{ $folder->id }}">
 <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded">
 <h6 class="mb-0 text-muted">{{ $folder->name }} Contents</h6>
 <button 
 class="btn btn-sm btn-primary"
 data-bs-toggle="modal" 
 data-bs-target="#createFolderModal"
 data-folder-id="{{ $folder->id }}"
 title="Create subfolder in {{ $folder->name }}">
 <i class="fas fa-plus me-1"></i> Create Folder
 </button>
 </div>
 <div class="folder-files p-2 border-start border-2 border-light">
 <p class="text-muted small">Files and subfolders will appear here</p>
 </div>
 </div>
 </li>
 @endforeach
</ul>

<!-- JavaScript moved to main view for better accessibility -->

<style>
.folder-tree {
 margin: 0;
 padding: 0;
}

.folder-item {
 border-left: 1px solid #dee2e6;
 margin-left: 8px;
}

.folder-node {
 padding: 4px 8px;
 cursor: pointer;
 transition: background-color 0.2s;
}

.folder-node:hover {
 background-color: #f8f9fa;
}

.folder-children {
 border-left: 1px solid #dee2e6;
 margin-left: 8px;
}

.folder-actions {
 opacity: 0;
 transition: opacity 0.2s;
}

.folder-node:hover .folder-actions {
 opacity: 1;
}

.folder-content-area {
 background-color: #f8f9fa;
 border-radius: 4px;
 margin-top: 4px;
}

.folder-files {
 min-height: 60px;
 background-color: white;
 border-radius: 4px;
}

.folder-name {
 font-weight: 500;
}
</style>