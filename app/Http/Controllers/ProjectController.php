<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Folder;
use App\Models\User;
use App\Models\ProjectCollaborator;
use App\Models\WorkspaceUser;
use App\Notifications\ProjectAccessShare;
use App\Notifications\ProjectRequestPending;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ProjectController extends Controller
{
    public function shareIndex(Request $request)
    {
        // Get projects shared with the current user (approved collaborations)
        $projects = ProjectCollaborator::where('user_id', $request->user()->id)
            ->where('status', 'approved')
            ->with(['project' => function ($query) {
                $query->with(['projectCollaborators' => function ($query) {
                    $query->where('status', 'approved');
                }, 'folders', 'owner']);
            }])
            ->get()
            ->pluck('project');
        
        return view('projects.share-index', compact('projects'));
    }

    public function show(Request $request, Project $project)
    {
        // Allow access if user is the owner
        if ($project->isOwnedBy($request->user())) {
            return $this->loadProjectView($project);
        }

        // Check if user is a workspace admin/owner (read-only access)
        if ($this->isWorkspaceAdmin($project, $request->user())) {
            return $this->loadProjectView($project, true);
        }

        // Check if user has approved access as project collaborator
        $hasAccess = ProjectCollaborator::where('project_id', $project->id)
            ->where('user_id', $request->user()->id)
            ->where('status', 'approved')
            ->exists();

        if (!$hasAccess) {
            abort(403, 'You do not have access to this project');
        }

        return $this->loadProjectView($project);
    }

    /**
     * Load project view with folders.
     */
    private function loadProjectView(Project $project, bool $readOnly = false)
    {
        $folders = Folder::where('project_id', $project->id)
            ->whereNull('parent_folder_id')
            ->with('children')
            ->get();

        return view('project.show', compact('project', 'folders', 'readOnly'));
    }

    /**
     * Check if user is a workspace admin or owner for this project.
     */
    private function isWorkspaceAdmin(Project $project, User $user): bool
    {
        return WorkspaceUser::where('workspace_id', $project->workspace_id)
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereIn('role', ['owner', 'admin'])
            ->exists();
    }

    public function share(Request $request, Project $project)
    {
        if (!$project->isOwnedBy($request->user())) {
            abort(403);
        }

        // Get current approved members from ProjectCollaborator table
        $approvedMembers = ProjectCollaborator::where('project_id', $project->id)
            ->where('status', 'approved')
            ->with('user')
            ->get();
        
        // Get pending project collaborator requests
        $pendingRequests = ProjectCollaborator::where('project_id', $project->id)
            ->where('status', 'pending')
            ->with('user')
            ->get();
        
        // Get rejected project collaborator requests
        $rejectedRequests = ProjectCollaborator::where('project_id', $project->id)
            ->where('status', 'rejected')
            ->with('user')
            ->get();
        
        // Load owner relationship
        $project->load('owner');
        
        return view('projects.share', compact('project', 'approvedMembers', 'pendingRequests', 'rejectedRequests'));
    }

    public function invite(Request $request, Project $project)
    {
        if (!$project->isOwnedBy($request->user())) {
            abort(403);
        }

        try {
            $validated = $request->validate([
                'email' => 'required|email|exists:users,email',
                'role' => 'required|in:admin,reviewer,viewer'
            ]);

            $user = User::where('email', $validated['email'])->first();            
            // Check if user is already a member (approved)
            $existingMembership = ProjectCollaborator::where('project_id', $project->id)
                ->where('user_id', $user->id)
                ->where('status', 'approved')
                ->first();

            if ($existingMembership) {
                return redirect()->back()
                    ->withErrors(['email' => 'User is already a member of this project'])
                    ->withInput();
            }

            // Check if there's already a pending request
            $existingPendingRequest = ProjectCollaborator::where('project_id', $project->id)
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->first();

            if ($existingPendingRequest) {
                return redirect()->back()
                    ->withErrors(['email' => 'A pending invitation already exists for this user'])
                    ->withInput();
            }
            // Create project collaborator record with pending status
            $projectCollaborator = ProjectCollaborator::create([
                'project_id' => $project->id,
                'user_id' => $user->id,
                'role' => $validated['role'],
                'status' => 'pending'
            ]);

            // Check if there are any existing pending requests for this project-user combination
            // to avoid duplicate notifications
            $existingNotifications = $user->notifications()
                ->where('type', ProjectAccessShare::class)
                ->where('data->project_id', $project->id)
                ->where('data->status', 'pending')
                ->whereNull('read_at')
                ->exists();

            // Only send notification if no existing pending notification exists
            if (!$existingNotifications) {
                // Send notification to the invited user that invitation has been sent
                $user->notify(new ProjectAccessShare($project, $request->user(), 'pending'));
                
                // Send notification to project owner about the pending request
                $project->owner->notify(new ProjectRequestPending($projectCollaborator, $user));
            }

            return redirect()->route('projects.share', $project)
                ->with('success', 'Invitation sent successfully. The user can now access the project.');

        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }
    }

    public function removeMember(Request $request, Project $project, User $user)
    {
        if (!$project->isOwnedBy($request->user())) {
            abort(403);
        }

        // Prevent removing the owner
        if ($project->created_by === $user->id) {
            abort(403, 'Cannot remove the project owner');
        }

        // Remove all project collaborator records for this user (both approved and pending)
        ProjectCollaborator::where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->delete();

        return redirect()->route('projects.share', $project)
            ->with('success', 'Member removed successfully');
    }

    public function acceptInvitation(Request $request, Project $project)
    {
        $user = $request->user();
        
        // Find the pending project collaborator record
        $projectCollaborator = ProjectCollaborator::where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$projectCollaborator) {
            return back()->with('error', 'No pending invitation found for this project.');
        }

        // Update the collaborator record to approved status
        $projectCollaborator->status = 'approved';
        $projectCollaborator->invited_at = now();
        $projectCollaborator->approved_at = now();
        $projectCollaborator->save();

        return back()->with('success', '✅ You have successfully joined the project!');
    }

    public function declineInvitation(Request $request, $notificationId)
    {
        $user = $request->user();
        
        // Find the notification and get project details
        $notification = $user->notifications()->findOrFail($notificationId);
        $projectId = $notification->data['project_id'] ?? null;
        
        if (!$projectId) {
            return back()->with('error', 'Invalid invitation.');
        }

        // Remove the pending project collaborator record
        ProjectCollaborator::where('project_id', $projectId)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->delete();

        // Mark the notification as read
        $notification->markAsRead();

        return back()->with('success', 'You have declined the project invitation.');
    }
}