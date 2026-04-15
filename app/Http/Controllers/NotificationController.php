<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkspaceUser;
use App\Models\ProjectCollaborator;
use App\Notifications\AccessShare;
use App\Notifications\WorkspaceRequestApproved;
use App\Notifications\WorkspaceInvitationResponseNotification;
use App\Notifications\ProjectRequestApproved;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user.
     */
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            $notifications = $request->user()
                ->notifications()
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'data' => $notification->data,
                        'read_at' => $notification->read_at,
                        'created_at' => $notification->created_at->toISOString(),
                    ];
                });

            return response()->json($notifications);
        }

        $notifications = $request->user()
            ->notifications()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Get last 10 notifications for dropdown (for performance).
     */
    public function recent(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'data' => $notification->data,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->toISOString(),
                ];
            });

        return response()->json($notifications);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Notification marked as read']);
        }

        return back()->with('success', 'Notification marked as read');
    }

    /**
     * Mark a specific notification as unread.
     */
    public function markAsUnread(Request $request, $id): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);

        $notification->read_at = null;
        $notification->save();

        return response()->json(['message' => 'Notification marked as unread']);
    }

    /**
     * Mark all notifications as read for the authenticated user.
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'All notifications marked as read']);
        }

        return back()->with('success', 'All notifications marked as read');
    }

    /**
     * Get unread notifications count for the authenticated user.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = $request->user()->unreadNotifications()->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Approve a workspace invitation.
     */
    public function approveInvitation(Request $request, $id)
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);

        $workspaceId = $notification->data['workspace_id'] ?? null;
        
        if (!$workspaceId) {
            return back()->with('error', 'Invalid invitation');
        }

        $workspace = \App\Models\Workspace::findOrFail($workspaceId);
        
        // Get the workspace user record if it exists
        $workspaceUser = WorkspaceUser::where('workspace_id', $workspaceId)
            ->where('user_id', $request->user()->id)
            ->first();
        
        if ($workspaceUser) {
            // Update the status to approved
            $workspaceUser->status = 'approved';
            $workspaceUser->save();
            $role = $workspaceUser->role;
        }else{
           return back()->with('error', 'Invalid invitation'); 
        }
        

        // Update notification with approval timestamp and change message
        $notification->approve_at = now(); // Set approval timestamp
        $notification->sent_at = null; // Clear any rejection
        
        // Update the notification data to show the approved message
        $notificationData = $notification->data;
        $notificationData['message'] = 'You have joined the workspace: ' . $workspace->name . ' (Approved)';
        $notification->data = $notificationData;
        
        $notification->markAsRead();

        // Send notification to the workspace owner about the acceptance
        \Log::info('Sending workspace acceptance notification to owner: ' . $workspace->owner->id);
        try {
            $workspace->owner->notify(new WorkspaceInvitationResponseNotification($workspace, $request->user(), 'accepted', $workspace->owner));
            \Log::info('Workspace acceptance notification sent successfully');
        } catch (\Exception $e) {
            \Log::error('Failed to send workspace acceptance notification: ' . $e->getMessage());
        }

        return redirect()->route('workspaces.share-index')
            ->with('success', '✅ Workspace invitation approved successfully! You now have access to the workspace.');
    }

    /**
     * Reject a workspace invitation.
     */
    public function rejectInvitation(Request $request, $id)
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);

         $workspaceId = $notification->data['workspace_id'] ?? null;
        
        if (!$workspaceId) {
            return back()->with('error', 'Invalid invitation');
        }

        $workspace = \App\Models\Workspace::findOrFail($workspaceId);
        
        // Get the workspace user record if it exists
        $workspaceUser = WorkspaceUser::where('workspace_id', $workspaceId)
            ->where('user_id', $request->user()->id)
            ->first();

        if($workspaceUser->status != 'pending'){
            return back()->with('error', 'Invitaion is already changed to '.$workspaceUser->status);
        }

        if ($workspaceUser) {
            $workspaceUser->delete();
        }else{
           return back()->with('error', 'Invalid invitation'); 
        }

        // Update notification with rejection timestamp
        $notification->approve_at = null; // Clear any approval
        $notification->sent_at = now(); // Set rejection timestamp
        $notification->markAsRead();

        // Send notification to the workspace owner about the rejection
        \Log::info('Sending workspace rejection notification to owner: ' . $workspace->owner->id);
        try {
            $workspace->owner->notify(new WorkspaceInvitationResponseNotification($workspace, $request->user(), 'rejected', $workspace->owner));
            \Log::info('Workspace rejection notification sent successfully');
        } catch (\Exception $e) {
            \Log::error('Failed to send workspace rejection notification: ' . $e->getMessage());
        }

        return back()->with('success', '❌ Workspace invitation rejected. The invitation has been removed.');
    }

    /**
     * Approve a workspace request.
     */
    public function approveWorkspaceRequest(Request $request, $id)
    {
        // Simple debug to verify method is called
        \Log::info('approveWorkspaceRequest method called with WorkspaceUser ID: ' . $id);
        
        // Find the workspace user record
        $workspaceUser = WorkspaceUser::findOrFail($id);
        if($workspaceUser->status != 'pending'){
            return back()->with('error', 'Invitaion is already changed to '.$workspaceUser->status);
        }
        
        // Check if the current user is the workspace owner
        $workspace = \App\Models\Workspace::findOrFail($workspaceUser->workspace_id);
        if ($workspace->owner_id !== $request->user()->id) {
            abort(403, 'Unauthorized action');
        }
        
        \Log::info('Processing workspace approval - Workspace ID: ' . $workspaceUser->workspace_id . ', User ID: ' . $workspaceUser->user_id);
        
        try {
            // Approve the workspace user request
            $workspaceUser->status = 'approved';
            $workspaceUser->save();

            \Log::info('Workspace user updated to status: ' . $workspaceUser->status);

            // Add user to workspace members (for backward compatibility and access control)
            $workspace->members()->syncWithoutDetaching([
                $workspaceUser->user_id => ['role' => $workspaceUser->role]
            ]);

            // Send approval notification to the user
            $user = User::findOrFail($workspaceUser->user_id);
            // Find and update the original notification
            $notification = $user->notifications()
                ->where('data->workspace_id', $workspaceUser->workspace_id)
                ->where('data->user_id', $workspaceUser->user_id)
                ->whereNull('read_at')
                ->first();

            if ($notification) {
                $notification->approve_at = now(); // Set approval timestamp
                $notification->sent_at = null; // Clear any rejection
                $notification->save();
            }

            return back()->with('success', '✅ Workspace request approved! The user has been granted access to the workspace.');

        } catch (\Exception $e) {
            \Log::error('Error approving workspace request: ' . $e->getMessage());
            return back()->with('error', 'Error approving workspace request: ' . $e->getMessage());
        }
    }

    /**
     * Reject a workspace request.
     */
    public function rejectWorkspaceRequest(Request $request, $id)
    {
        // Simple debug to verify method is called
        \Log::info('rejectWorkspaceRequest method called with WorkspaceUser ID: ' . $id);
        
        // Find the workspace user record
        $workspaceUser = WorkspaceUser::findOrFail($id);
        
        // Check if the current user is the workspace owner
        $workspace = \App\Models\Workspace::findOrFail($workspaceUser->workspace_id);
        if ($workspace->owner_id !== $request->user()->id) {
            abort(403, 'Unauthorized action');
        }
        
        \Log::info('Processing workspace rejection - Workspace ID: ' . $workspaceUser->workspace_id . ', User ID: ' . $workspaceUser->user_id);
        \Log::info('Current status before update: ' . $workspaceUser->status);
        
        try {
            // Reject the workspace user request
            // $workspaceUser->status = 'rejected';
            $workspaceUser->delete();

            \Log::info('Workspace user deleted');
            
            // Verify the save worked by refreshing the model
            $workspaceUser->refresh();
            \Log::info('Verified status after refresh: ' . $workspaceUser->status);

            // Remove user from workspace members since they are rejected
            $workspace->members()->detach($workspaceUser->user_id);

            // Send rejection notification to the user
            $user = User::findOrFail($workspaceUser->user_id);

            // Find and update the original notification
            $notifications = $user->notifications()
                ->where('data->workspace_id', $workspaceUser->workspace_id)
                ->whereNull('read_at')
                ->get();

            \Log::info('Found ' . $notifications->count() . ' notifications for workspace ' . $workspaceUser->workspace_id);
            
            foreach ($notifications as $notification) {
                \Log::info('Notification data: ' . json_encode($notification->data));
                if ($notification->data['status'] === 'pending') {
                    \Log::info('Deleting notification ID: ' . $notification->id);
                    $notification->delete();
                }
            }

            return back()->with('success', '❌ Workspace request rejected. The user has been denied access to the workspace.');

        } catch (\Exception $e) {
            \Log::error('Error rejecting workspace request: ' . $e->getMessage());
            return back()->with('error', 'Error rejecting workspace request: ' . $e->getMessage());
        }
    }

    /**
     * Approve a project request.
     */
    public function approveProjectRequest(Request $request, $id)
    {
        // Simple debug to verify method is called
        \Log::info('approveProjectRequest method called with ID: ' . $id);
        
        try {
            // Find the project collaborator record
            $projectCollaborator = ProjectCollaborator::findOrFail($id);
            \Log::info('Found ProjectCollaborator: ' . $projectCollaborator->id . ', Status: ' . $projectCollaborator->status);
            
            if($projectCollaborator->status != 'pending'){
                return back()->with('error', 'Invitation is already changed to '.$projectCollaborator->status);
            }
            
            // Check if the current user is the project owner
            $project = \App\Models\Project::findOrFail($projectCollaborator->project_id);
            if ($project->created_by !== $request->user()->id) {
                abort(403, 'Unauthorized action');
            }
            
            \Log::info('Processing project approval - Project ID: ' . $projectCollaborator->project_id . ', User ID: ' . $projectCollaborator->user_id);
            
            // Approve the project collaborator request
            $projectCollaborator->status = 'approved';
            $projectCollaborator->approved_at = now();
            $projectCollaborator->save();

            \Log::info('Project collaborator updated to status: ' . $projectCollaborator->status);

            // Send approval notification to the user
            $user = User::findOrFail($projectCollaborator->user_id);
            $user->notify(new ProjectRequestApproved($project));

            return back()->with('success', '✅ Project request approved! The user has been granted access to the project.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('ProjectCollaborator not found with ID: ' . $id);
            return back()->with('error', 'Invalid invitation: Project collaborator not found');
        } catch (\Exception $e) {
            \Log::error('Error approving project request: ' . $e->getMessage());
            return back()->with('error', 'Error approving project request: ' . $e->getMessage());
        }
    }

    /**
     * Reject a project request.
     */
    public function rejectProjectRequest(Request $request, $id)
    {
        // Simple debug to verify method is called
        \Log::info('rejectProjectRequest method called with ID: ' . $id);
        
        try {
            // Find the project collaborator record
            $projectCollaborator = ProjectCollaborator::findOrFail($id);
            \Log::info('Found ProjectCollaborator: ' . $projectCollaborator->id . ', Status: ' . $projectCollaborator->status);
            
            // Check if the current user is the project owner
            $project = \App\Models\Project::findOrFail($projectCollaborator->project_id);
            if ($project->created_by !== $request->user()->id) {
                abort(403, 'Unauthorized action');
            }
            
            \Log::info('Processing project rejection - Project ID: ' . $projectCollaborator->project_id . ', User ID: ' . $projectCollaborator->user_id);
            \Log::info('Current status before update: ' . $projectCollaborator->status);
            
            // Reject the project collaborator request
            $projectCollaborator->status = 'rejected';
            $projectCollaborator->save();

            \Log::info('Project collaborator updated to status: ' . $projectCollaborator->status);

            return back()->with('success', '❌ Project request rejected. The user has been denied access to the project.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('ProjectCollaborator not found with ID: ' . $id);
            return back()->with('error', 'Invalid invitation: Project collaborator not found');
        } catch (\Exception $e) {
            \Log::error('Error rejecting project request: ' . $e->getMessage());
            return back()->with('error', 'Error rejecting project request: ' . $e->getMessage());
        }
    }
}
