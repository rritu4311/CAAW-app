<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\Project;
use App\Models\User;
use App\Models\WorkspaceUser;
use App\Notifications\AccessShare;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class WorkspaceController extends Controller
{
    public function index(Request $request)
    {
        // Get workspaces where user has approved access (including owned workspaces)
        $workspaces = Workspace::whereHas('workspaceUsers', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id)
                  ->where('status', 'approved');
        })->orWhere('owner_id', $request->user()->id)->get();
        
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
            
            // Create WorkspaceUser record with approved status for owner
            WorkspaceUser::create([
                'workspace_id' => $workspace->id,
                'user_id' => $request->user()->id,
                'role' => 'owner',
                'status' => 'approved'
            ]);

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
        // Allow access if user is the owner
        if ($workspace->isOwnedBy($request->user())) {
            return view('workspace.index', compact('workspace'));
        }
        
        // Check if user has approved access
        $hasAccess = WorkspaceUser::where('workspace_id', $workspace->id)
            ->where('user_id', $request->user()->id)
            ->where('status', 'approved')
            ->exists();
            
        if (!$hasAccess) {
            abort(403, 'You do not have access to this workspace');
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

        // Get current approved members from WorkspaceUser table
        $approvedMembers = WorkspaceUser::where('workspace_id', $workspace->id)
            ->where('status', 'approved')
            ->with('user')
            ->get();
        
        // Get pending workspace user requests
        $pendingRequests = WorkspaceUser::where('workspace_id', $workspace->id)
            ->where('status', 'pending')
            ->with('user')
            ->get();
        
        // Get rejected workspace user requests
        $rejectedRequests = WorkspaceUser::where('workspace_id', $workspace->id)
            ->where('status', 'rejected')
            ->with('user')
            ->get();
        
        // Load owner relationship
        $workspace->load('owner');
        
        return view('workspaces.share', compact('workspace', 'approvedMembers', 'pendingRequests', 'rejectedRequests'));
    }

    public function invite(Request $request, Workspace $workspace)
    {
        if (!$workspace->isOwnedBy($request->user())) {
            abort(403);
        }

        try {
            $validated = $request->validate([
                'email' => 'required|email|exists:users,email',
                'role' => 'required|in:member,admin'
            ]);

            $user = User::where('email', $validated['email'])->first();            
            // Check if user is already a member (approved)
            $existingMembership = WorkspaceUser::where('workspace_id', $workspace->id)
                ->where('user_id', $user->id)
                ->where('status', 'approved')
                ->first();

            if ($existingMembership) {
                return redirect()->back()
                    ->withErrors(['email' => 'User is already a member of this workspace'])
                    ->withInput();
            }

            // Check if there's already a pending request
            $existingPendingRequest = WorkspaceUser::where('workspace_id', $workspace->id)
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->first();

            if ($existingPendingRequest) {
                return redirect()->back()
                    ->withErrors(['email' => 'A pending invitation already exists for this user'])
                    ->withInput();
            }
            // Create workspace user record with pending status
            $workspaceUser = WorkspaceUser::create([
                'workspace_id' => $workspace->id,
                'user_id' => $user->id,
                'role' => $validated['role'],
                'status' => 'pending'
            ]);

            // Check if there are any existing pending requests for this workspace-user combination
            // to avoid duplicate notifications
            $existingNotifications = $user->notifications()
                ->where('type', AccessShare::class)
                ->where('data->workspace_id', $workspace->id)
                ->where('data->status', 'pending')
                ->whereNull('read_at')
                ->exists();

            // Only send notification if no existing pending notification exists
            if (!$existingNotifications) {
                // Send notification to the invited user that invitation has been sent
                $user->notify(new AccessShare($workspace, $request->user(), 'pending'));
                
                // Send notification to workspace owner about the pending request
                $workspace->owner->notify(new \App\Notifications\WorkspaceRequestPending($workspaceUser, $user));
            }

            return redirect()->route('workspaces.share', $workspace)
                ->with('success', 'Invitation sent successfully. The user can now access the workspace.');

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

        // Remove all workspace user records for this user (both approved and pending)
        WorkspaceUser::where('workspace_id', $workspace->id)
            ->where('user_id', $user->id)
            ->delete();

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
