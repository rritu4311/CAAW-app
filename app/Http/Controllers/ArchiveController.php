<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Workspace;

class ArchiveController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get archived projects owned by user
        $ownedProjects = Project::where('status', 'archived')
            ->where('created_by', $user->id)
            ->with(['workspace', 'folders'])
            ->orderBy('updated_at', 'desc')
            ->get();
        
        // Get archived projects where user is a collaborator
        $collaboratorProjects = Project::where('status', 'archived')
            ->whereHas('projectCollaborators', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('status', 'approved');
            })
            ->with(['workspace', 'folders', 'owner'])
            ->orderBy('updated_at', 'desc')
            ->get();
        
        // Get archived projects from workspaces where user has access
        $workspaceProjects = Project::where('status', 'archived')
            ->whereHas('workspace.workspaceUsers', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('status', 'approved')
                    ->whereIn('role', ['owner', 'admin', 'user']);
            })
            ->with(['workspace', 'folders', 'owner'])
            ->orderBy('updated_at', 'desc')
            ->get();
        
        // Merge all collections and remove duplicates
        $projects = $ownedProjects
            ->merge($collaboratorProjects)
            ->merge($workspaceProjects)
            ->unique('id')
            ->sortByDesc('updated_at');
        
        return view('archive.index', compact('projects'));
    }

    public function workspaceArchive(Request $request, Workspace $workspace)
    {
        $user = $request->user();

        // Check if user has access to this workspace
        if (!$workspace->isOwnedBy($user) && !$workspace->userHasRole($user, ['admin', 'user', 'member'])) {
            abort(403);
        }

        // Get archived projects from this specific workspace
        $projects = Project::where('status', 'archived')
            ->where('workspace_id', $workspace->id)
            ->with(['workspace', 'folders', 'owner'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('archive.workspace', compact('projects', 'workspace'));
    }
}
