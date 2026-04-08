<?php

namespace App\Http\Controllers;
use App\Models\Project;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class WorkareaController extends Controller
{
    public function index(Request $request, Workspace $workspace)
    {
        $user = $request->user();

        // Check if user has access to this workspace
        if (!$workspace->isOwnedBy($user) && !$workspace->userHasRole($user, ['admin', 'user', 'member'])) {
            abort(403);
        }

        $isOwner = $workspace->isOwnedBy($user);
        $isAdmin = $workspace->userHasRole($user, ['admin']);
        $isWorkspaceUser = $workspace->userHasRole($user, ['user', 'member']);

        // Load only active (non-archived) projects
        $workspace->load(['projects' => function ($query) {
            $query->where('status', '!=', 'archived')
                  ->orWhereNull('status');
        }]);

        return view('workspace.index', compact('workspace', 'isOwner', 'isAdmin', 'isWorkspaceUser'));
    }

    public function store(Request $request, Workspace $workspace)
    {
        $user = $request->user();

        // Owner, Admin, and User can create projects
        if (!$workspace->canUserCreateProject($user)) {
            abort(403);
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:projects,name,NULL,id,workspace_id,' . $workspace->id,
                'client_name' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'deadline' => 'nullable|date|after_or_equal:today'
            ]);

            $validated['workspace_id'] = $workspace->id;
            // Project owner should always be the workspace owner, not the user who created it
            $validated['created_by'] = $workspace->owner_id;

            Project::create($validated);

            return redirect()->route('workspace.page', $workspace)
                ->with('success', 'Project created successfully');

        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }
    }

    public function show(Request $request, Workspace $workspace, Project $project)
    {
        $user = $request->user();

        // All workspace members can view projects
        if (!$workspace->canUserViewProjects($user) || $project->workspace_id !== $workspace->id) {
            abort(403);
        }

        return view('project.show', compact('workspace', 'project'));
    }

    
    public function update(Request $request, Workspace $workspace, Project $project)
    {
        $user = $request->user();

        // Owner and User have full access (can edit any project)
        // Admin: read-only (cannot edit projects)
        $canEdit = $workspace->isOwnedBy($user) || $workspace->userHasRole($user, ['user']) || $project->isOwnedBy($user);

        if (!$canEdit || $project->workspace_id !== $workspace->id) {
            abort(403);
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:projects,name,' . $project->id . ',id,workspace_id,' . $workspace->id,
                'client_name' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'deadline' => 'nullable|date|after_or_equal:today'
            ]);

            $project->update($validated);

            return redirect()->route('workspace.page', $workspace)
                ->with('success', 'Project updated successfully');

        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }
    }

    public function destroy(Request $request, Workspace $workspace, Project $project)
    {
        $user = $request->user();

        // Owner and User have full access (can delete any project)
        // Admin: read-only (cannot delete projects)
        $canDelete = $workspace->isOwnedBy($user) || $workspace->userHasRole($user, ['user']) || $project->isOwnedBy($user);

        if (!$canDelete || $project->workspace_id !== $workspace->id) {
            abort(403);
        }

        $project->delete();

        return redirect()->route('workspace.page', $workspace)
            ->with('success', 'Project deleted successfully');
    }
    
    
}
