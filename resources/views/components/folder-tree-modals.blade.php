@foreach($folders as $folder)
 <x-create-folder-modal :projectId="$projectId" :parentFolderId="$folder->id" />
 @if($folder->children->count() > 0)
 @include('components.folder-tree-modals', ['folders' => $folder->children, 'projectId' => $projectId])
 @endif
@endforeach
