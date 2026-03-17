<?php

namespace App\Http\Controllers;
use App\Models\Folder;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'project_id' => 'required|exists:projects,id',
            'parent_folder_id' => 'nullable|exists:folders,id'
        ]);

        Folder::create([
            'name' => $request->name,
            'project_id' => $request->project_id,
            'parent_folder_id' => $request->parent_folder_id,
            'order' => Folder::where('parent_folder_id', $request->parent_folder_id)->count()
        ]);

        return back()->with('success','Folder created');
    }

    public function show(Folder $folder)
    {
        // Load folder with children and parent hierarchy
        $folder->load(['children', 'parent', 'project']);
        
        // Get breadcrumb path
        $breadcrumbs = $this->getBreadcrumbs($folder);
        
        return view('folder.show', compact('folder', 'breadcrumbs'));
    }

    private function getBreadcrumbs(Folder $folder)
    {
        $breadcrumbs = [];
        $current = $folder;
        
        while ($current) {
            array_unshift($breadcrumbs, $current);
            $current = $current->parent;
        }
        
        return $breadcrumbs;
    }

    public function destroy(Folder $folder)
    {
        $this->deleteFolderRecursive($folder);
        return back()->with('success','Folder deleted successfully');
    }

    private function deleteFolderRecursive(Folder $folder)
    {
        foreach ($folder->children as $child) {
            $this->deleteFolderRecursive($child);
        }
        $folder->delete();
    }
}
