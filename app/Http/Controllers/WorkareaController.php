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
        if (!$workspace->isOwnedBy($request->user())) {
            abort(403);
        }

        return view('workspace.index', compact('workspace'));
    }

    public function store(Request $request, Workspace $workspace)
    {
        // Allow owner or workspace 'user' role members to create projects
        $isWorkspaceUser = \App\Models\WorkspaceUser::where('workspace_id', $workspace->id)
            ->where('user_id', $request->user()->id)
            ->where('status', 'approved')
            ->where('role', 'user')
            ->exists();
            
        if (!$workspace->isOwnedBy($request->user()) && !$isWorkspaceUser) {
            abort(403);
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'client_name' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'deadline' => 'nullable|date|after_or_equal:today'
            ]);

            $validated['workspace_id'] = $workspace->id;
            $validated['created_by'] = $request->user()->id;

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
        if (!$workspace->isOwnedBy($request->user()) || $project->workspace_id !== $workspace->id) {
            abort(403);
        }

        return view('project.show', compact('workspace', 'project'));
    }

    
    public function update(Request $request, Workspace $workspace, Project $project)
    {
        // Allow owner or workspace 'user' role members to edit projects
        $isWorkspaceUser = \App\Models\WorkspaceUser::where('workspace_id', $workspace->id)
            ->where('user_id', $request->user()->id)
            ->where('status', 'approved')
            ->where('role', 'user')
            ->exists();
            
        if ((!$workspace->isOwnedBy($request->user()) && !$isWorkspaceUser) || $project->workspace_id !== $workspace->id) {
            abort(403);
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
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
        // Allow owner or workspace 'user' role members to delete projects
        $isWorkspaceUser = \App\Models\WorkspaceUser::where('workspace_id', $workspace->id)
            ->where('user_id', $request->user()->id)
            ->where('status', 'approved')
            ->where('role', 'user')
            ->exists();
            
        if ((!$workspace->isOwnedBy($request->user()) && !$isWorkspaceUser) || $project->workspace_id !== $workspace->id) {
            abort(403);
        }

        $project->delete();

        return redirect()->route('workspace.page', $workspace)
            ->with('success', 'Project deleted successfully');
    }
    
    
}
