<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Folder;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function show($id)
    {
        $project = Project::findOrFail($id);

        // Load root folders with their children
        $folders = Folder::where('project_id', $project->id)
            ->whereNull('parent_folder_id')
            ->with('children')
            ->get();

        return view('project.show', compact('project', 'folders'));
    }
}