<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\Project;
use App\Models\User;
use App\Models\WorkspaceUser;
use App\Notifications\AccessShare;
use App\Notifications\MemberRemoved;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class WorkspaceController extends Controller
{
    public function index(Request $request)
    {
        // Get only workspaces owned by the user (not shared ones)
        $workspaces = Workspace::where('owner_id', $request->user()->id)->get();
        
        return view('workspaces.index', compact('workspaces'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:workspaces,name,NULL,id,owner_id,' . $request->user()->id
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
        $user = $request->user();

        // Check if user is owner
        $isOwner = $workspace->isOwnedBy($user);

        // Check if user is admin
        $isAdmin = $workspace->userHasRole($user, ['admin']);

        // Check if user has 'user' or 'member' role in workspace
        $isWorkspaceUser = $workspace->userHasRole($user, ['user', 'member']);

        // Check if user has approved access
        $hasAccess = $workspace->userHasRole($user, ['admin', 'user', 'member']);

        if (!$isOwner && !$hasAccess) {
            abort(403, 'You do not have access to this workspace');
        }

        return view('workspace.index', compact('workspace', 'isOwner', 'isAdmin', 'isWorkspaceUser'));
    }

    public function update(Request $request, Workspace $workspace)
    {
        if (!$workspace->isOwnedBy($request->user())) {
            abort(403);
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:workspaces,name,' . $workspace->id . ',id,owner_id,' . $request->user()->id
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
        if (!$this->hasAdminAccess($workspace, $request->user())) {
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
        if (!$this->hasAdminAccess($workspace, $request->user())) {
            abort(403);
        }

        try {
            $validated = $request->validate([
                'email' => 'required|email|exists:users,email',
                'role' => 'required|in:user,admin'
            ]);

            // Only workspace owner can invite admins
            if ($validated['role'] === 'admin' && !$workspace->isOwnedBy($request->user())) {
                return redirect()->back()
                    ->withErrors(['role' => 'Only the workspace owner can invite admins'])
                    ->withInput();
            }

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
        if (!$this->hasAdminAccess($workspace, $request->user())) {
            abort(403);
        }

        // Prevent removing the owner
        if ($workspace->owner_id === $user->id) {
            abort(403, 'Cannot remove the workspace owner');
        }

        // Admins cannot remove other admins or the owner
        if (!$workspace->isOwnedBy($request->user())) {
            $targetUser = WorkspaceUser::where('workspace_id', $workspace->id)
                ->where('user_id', $user->id)
                ->first();
            if ($targetUser && ($targetUser->role === 'owner' || $targetUser->role === 'admin')) {
                abort(403, 'Admins can only remove regular members');
            }
        }

        // Notify workspace members about the member removal
        $this->notifyWorkspaceMembers(
            $workspace,
            new MemberRemoved($workspace->name, 'workspace', $user->name, $request->user()),
            [$request->user()->id, $user->id] // Exclude the remover and the removed user
        );

        // Remove all workspace user records for this user (both approved and pending)
        WorkspaceUser::where('workspace_id', $workspace->id)
            ->where('user_id', $user->id)
            ->delete();

        return redirect()->route('workspaces.share', $workspace)
            ->with('success', 'Member removed successfully');
    }

    public function updateMemberRole(Request $request, Workspace $workspace, User $user)
    {
        if (!$this->hasAdminAccess($workspace, $request->user())) {
            abort(403);
        }

        // Prevent editing the owner
        if ($workspace->owner_id === $user->id) {
            abort(403, 'Cannot change the workspace owner role');
        }

        // Only workspace owner can assign admin role
        if (!$workspace->isOwnedBy($request->user())) {
            $targetUser = WorkspaceUser::where('workspace_id', $workspace->id)
                ->where('user_id', $user->id)
                ->first();
            if ($targetUser && $targetUser->role === 'admin') {
                abort(403, 'Only the workspace owner can change admin roles');
            }
        }

        $validated = $request->validate([
            'role' => 'required|in:user,admin'
        ]);

        // Only owner can set admin role
        if ($validated['role'] === 'admin' && !$workspace->isOwnedBy($request->user())) {
            return redirect()->back()
                ->withErrors(['role' => 'Only the workspace owner can assign admin role']);
        }

        WorkspaceUser::where('workspace_id', $workspace->id)
            ->where('user_id', $user->id)
            ->update(['role' => $validated['role']]);

        return redirect()->route('workspaces.share', $workspace)
            ->with('success', 'Member role updated successfully');
    }

    /**
     * Check if user has admin access to workspace (owner or admin role).
     */
    private function hasAdminAccess(Workspace $workspace, User $user): bool
    {
        if ($workspace->isOwnedBy($user)) {
            return true;
        }

        $workspaceUser = WorkspaceUser::where('workspace_id', $workspace->id)
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->first();

        return $workspaceUser && ($workspaceUser->role === 'admin' || $workspaceUser->role === 'owner');
    }

    /**
     * Check if user has read-only access to workspace.
     */
    private function hasReadAccess(Workspace $workspace, User $user): bool
    {
        if ($workspace->isOwnedBy($user)) {
            return true;
        }

        return WorkspaceUser::where('workspace_id', $workspace->id)
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->exists();
    }

    public function shareIndex(Request $request)
    {
        $userId = $request->user()->id;
        
        // Debug: Check all workspace users for this user
        $allWorkspaceUsers = WorkspaceUser::where('user_id', $userId)->get();
        \Log::info('All workspace users for user ' . $userId . ':');
        foreach ($allWorkspaceUsers as $wu) {
            \Log::info('  - Workspace ID: ' . $wu->workspace_id . ', Status: ' . $wu->status . ', Role: ' . $wu->role);
        }
        
        // Get workspaces shared with the current user (approved workspace users - EXCLUDING owned)
        $workspaceUsers = WorkspaceUser::where('user_id', $userId)
            ->where('status', 'approved')
            ->whereHas('workspace', function ($query) use ($userId) {
                $query->where('owner_id', '!=', $userId);
            })
            ->with(['workspace' => function ($query) {
                $query->with(['workspaceUsers' => function ($query) {
                    $query->where('status', 'approved');
                }, 'projects', 'owner']);
            }])
            ->get();
        
        // Debug: Log the workspace users found
        \Log::info('Approved workspace users for user ' . $userId . ': ' . $workspaceUsers->count());
        
        $workspaces = $workspaceUsers->pluck('workspace');
        
        // Debug: Log the workspaces found
        \Log::info('Workspaces found: ' . $workspaces->count());
        
        // Get pending workspace invitations for this user (separate from approved)
        $pendingWorkspaceUsers = WorkspaceUser::where('user_id', $userId)
            ->where('status', 'pending')
            ->with(['workspace' => function ($query) {
                $query->with('owner');
            }])
            ->get();
        
        $pendingWorkspaces = $pendingWorkspaceUsers->pluck('workspace');
        \Log::info('Pending workspaces for user ' . $userId . ': ' . $pendingWorkspaces->count());
        
        return view('workspaces.share-index', compact('workspaces', 'pendingWorkspaces'));
    }

    public function activityLog(Request $request)
    {
        $user = $request->user();

        // Get activities from the Spatie activity_log table
        $activities = \Spatie\Activitylog\Models\Activity::where('causer_id', $user->id)
            ->where('causer_type', get_class($user))
            ->with('subject')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('activity-log', compact('activities'));
    }

    /**
     * Bulk invite via CSV upload for workspace.
     */
    public function bulkInvite(Request $request, Workspace $workspace)
    {
        if (!$this->hasAdminAccess($workspace, $request->user())) {
            abort(403);
        }

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
            'role' => 'required|in:user,admin'
        ]);

        $file = $request->file('csv_file');
        $defaultRole = $request->input('role');

        // Only workspace owner can invite admins
        if ($defaultRole === 'admin' && !$workspace->isOwnedBy($request->user())) {
            return redirect()->back()
                ->withErrors(['role' => 'Only the workspace owner can invite admins'])
                ->withInput();
        }

        // Parse CSV file
        $csvData = [];
        $handle = fopen($file->getPathname(), 'r');
        
        if ($handle !== false) {
            // Skip header row if it exists
            $header = fgetcsv($handle);
            
            // Check if header contains 'email' column
            $hasHeader = $header && in_array('email', array_map('strtolower', $header));
            
            if (!$hasHeader) {
                // If no header, rewind to start
                rewind($handle);
            }
            
            while (($row = fgetcsv($handle)) !== false) {
                if ($hasHeader) {
                    // Map columns by header names
                    $email = $row[array_search('email', array_map('strtolower', $header))] ?? null;
                    $role = $row[array_search('role', array_map('strtolower', $header))] ?? $defaultRole;
                } else {
                    // Assume first column is email, second is role (optional)
                    $email = $row[0] ?? null;
                    $role = $row[1] ?? $defaultRole;
                }
                
                if ($email) {
                    $csvData[] = [
                        'email' => trim($email),
                        'role' => trim($role) ?: $defaultRole
                    ];
                }
            }
            fclose($handle);
        }

        $invitedCount = 0;
        $skippedCount = 0;

        foreach ($csvData as $data) {
            $email = $data['email'];
            $role = $data['role'];

            // Validate role
            if (!in_array($role, ['user', 'admin'])) {
                $role = $defaultRole;
            }

            // Only workspace owner can invite admins
            if ($role === 'admin' && !$workspace->isOwnedBy($request->user())) {
                $role = 'user';
            }

            $user = User::where('email', $email)->first();

            if ($user) {
                // Check if user is already a member
                $existingMembership = WorkspaceUser::where('workspace_id', $workspace->id)
                    ->where('user_id', $user->id)
                    ->where('status', 'approved')
                    ->first();

                if ($existingMembership) {
                    $skippedCount++;
                    continue;
                }

                // Check if there's already a pending request
                $existingPendingRequest = WorkspaceUser::where('workspace_id', $workspace->id)
                    ->where('user_id', $user->id)
                    ->where('status', 'pending')
                    ->first();

                if ($existingPendingRequest) {
                    $skippedCount++;
                    continue;
                }

                // Create workspace user record
                $workspaceUser = WorkspaceUser::create([
                    'workspace_id' => $workspace->id,
                    'user_id' => $user->id,
                    'role' => $role,
                    'status' => 'pending'
                ]);

                // Send notification
                $user->notify(new AccessShare($workspace, $request->user(), 'pending'));
                $invitedCount++;
            } else {
                $skippedCount++;
            }
        }

        return redirect()->route('workspaces.share', $workspace)
            ->with('success', "Bulk invite completed: {$invitedCount} invitations sent, {$skippedCount} skipped (users not found or already invited)");
    }

    /**
     * Notify workspace members about an event (excluding specified users).
     */
    private function notifyWorkspaceMembers(Workspace $workspace, $notification, array $excludeUserIds = []): void
    {
        try {
            $membersToNotify = collect();

            // Get workspace owner
            if ($workspace->owner && !in_array($workspace->owner->id, $excludeUserIds)) {
                $membersToNotify->push($workspace->owner);
            }

            // Get approved workspace members
            $workspaceUsers = WorkspaceUser::where('workspace_id', $workspace->id)
                ->where('status', 'approved')
                ->with('user')
                ->get();

            foreach ($workspaceUsers as $workspaceUser) {
                if ($workspaceUser->user && !in_array($workspaceUser->user->id, $excludeUserIds)) {
                    $membersToNotify->push($workspaceUser->user);
                }
            }

            // Send notifications
            foreach ($membersToNotify->unique('id') as $user) {
                $user->notify($notification);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to notify workspace members', [
                'workspace_id' => $workspace->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
