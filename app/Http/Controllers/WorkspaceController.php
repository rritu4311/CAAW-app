<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\Project;
use App\Models\User;
use App\Notifications\AccessShare;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class WorkspaceController extends Controller
{
    public function index(Request $request)
    {
        $workspaces = $request->user()->workspaces()->get();
        
        return view('workspaces.index', compact('workspaces'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255'
            ]);

            $validated['owner_id'] = $request->user()->id;
            $workspace = Workspace::create($validated);
            $workspace->members()->attach($request->user()->id, ['role' => 'owner']);

            return redirect()->route('workspaces.page')
                ->with('success', 'Workspace created successfully');

        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }
    }

    public function show(Request $request, Workspace $workspace)
    {
        if (!$workspace->isOwnedBy($request->user())) {
            abort(403);
        }

        return view('workspace.index', compact('workspace'));
    }

    public function update(Request $request, Workspace $workspace)
    {
        if (!$workspace->isOwnedBy($request->user())) {
            abort(403);
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255'
            ]);

            $workspace->update($validated);

            return redirect()->route('workspaces.page')
                ->with('success', 'Workspace updated successfully');

        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }
    }

    public function destroy(Request $request, Workspace $workspace)
    {
        if (!$workspace->isOwnedBy($request->user())) {
            abort(403);
        }

        $workspace->delete();

        return redirect()->route('workspaces.page')
            ->with('success', 'Workspace deleted successfully');
    }

    public function share(Request $request, Workspace $workspace)
    {
        if (!$workspace->isOwnedBy($request->user())) {
            abort(403);
        }

        // Get current members
        $members = $workspace->members()->withPivot('role')->get();
        
        // Load owner relationship
        $workspace->load('owner');
        
        return view('workspaces.share', compact('workspace', 'members'));
    }

    public function invite(Request $request, Workspace $workspace)
    {
        if (!$workspace->isOwnedBy($request->user())) {
            abort(403);
        }

        try {
            $validated = $request->validate([
                'email' => 'required|email|exists:users,email',
                'role' => 'required|in:user,admin'
            ]);

            $user = User::where('email', $validated['email'])->first();
            
            // Check if user is already a member
            if ($workspace->members()->where('user_id', $user->id)->exists()) {
                return redirect()->back()
                    ->withErrors(['email' => 'User is already a member of this workspace'])
                    ->withInput();
            }

            // Add user to workspace
            $workspace->members()->attach($user->id, ['role' => $validated['role']]);

            // Send notification
            $user->notify(new AccessShare($workspace, $request->user()));

            return redirect()->route('workspaces.share', $workspace)
                ->with('success', 'Member invited successfully');

        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }
    }

    public function removeMember(Request $request, Workspace $workspace, User $user)
    {
        if (!$workspace->isOwnedBy($request->user())) {
            abort(403);
        }

        // Prevent removing the owner
        if ($workspace->owner_id === $user->id) {
            abort(403, 'Cannot remove the workspace owner');
        }

        // Remove member
        $workspace->members()->detach($user->id);

        return redirect()->route('workspaces.share', $workspace)
            ->with('success', 'Member removed successfully');
    }

    public function activityLog(Request $request)
    {
        $user = $request->user();

        // Get all workspaces owned by the user
        $workspaces = $user->workspaces()->orderBy('created_at', 'desc')->get()->map(function ($item) {
            $item->type = 'workspace';
            return $item;
        });

        // Get all projects created by the user (across all workspaces)
        $projects = Project::where('created_by', $user->id)->orderBy('created_at', 'desc')->get()->map(function ($item) {
            $item->type = 'project';
            return $item;
        });

        // Get all folders (through projects owned by user)
        $folders = collect();
        foreach ($projects as $project) {
            $folders = $folders->merge($project->folders()->orderBy('created_at', 'desc')->get());
        }
        $folders = $folders->map(function ($item) {
            $item->type = 'folder';
            return $item;
        });

        // Get all assets (through folders)
        $assets = collect();
        foreach ($folders as $folder) {
            $assets = $assets->merge($folder->assets()->orderBy('created_at', 'desc')->get());
        }
        $assets = $assets->map(function ($item) {
            $item->type = 'asset';
            return $item;
        });

        // Combine all items and sort by creation time (most recent first)
        $activities = collect()
            ->merge($workspaces)
            ->merge($projects)
            ->merge($folders)
            ->merge($assets)
            ->sortByDesc('created_at')
            ->values();

        // Paginate the activities (10 per page)
        $perPage = 10;
        $currentPage = $request->get('page', 1);
        $paginatedActivities = new \Illuminate\Pagination\LengthAwarePaginator(
            $activities->forPage($currentPage, $perPage),
            $activities->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'pageName' => 'page']
        );

        return view('activity-log', compact('paginatedActivities'));
    }
}
