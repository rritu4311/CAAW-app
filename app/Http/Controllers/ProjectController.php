<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Folder;
use App\Models\User;
use App\Models\ProjectCollaborator;
use App\Models\WorkspaceUser;
use App\Notifications\ProjectAccessShare;
use App\Notifications\ProjectRequestPending;
use App\Notifications\ProjectInvitationResponseNotification;
use App\Notifications\ProjectArchived;
use App\Notifications\MemberRemoved;
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
                }, 'folders', 'owner'])
                ->where('status', '!=', 'archived'); // Filter out archived projects
            }])
            ->get()
            ->pluck('project')
            ->filter(function ($project) {
                return $project && $project->status !== 'archived';
            });
        
        return view('projects.share-index', compact('projects'));
    }

    public function show(Request $request, Project $project)
    {
        $user = $request->user();

        // Allow access if user has any project access (owner or approved collaborator)
        if (!$user->canViewProject($project)) {
            abort(403, 'You do not have access to this project');
        }

        // Determine read-only access (viewers have read-only, others can interact)
        $readOnly = !$user->canCommentOnProject($project);

        return $this->loadProjectView($project, $readOnly);
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

        $project->load('workflows');

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

    /**
     * Check if user is a workspace user with 'user' role for this project.
     */
    private function isWorkspaceUser(Project $project, User $user): bool
    {
        return WorkspaceUser::where('workspace_id', $project->workspace_id)
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('role', 'user')
            ->exists();
    }

    public function share(Request $request, Project $project)
    {
        if (!$request->user()->canManageCollaborators($project)) {
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
        if (!$request->user()->canManageCollaborators($project)) {
            abort(403);
        }

        try {
            $validated = $request->validate([
                'email' => 'required|email|exists:users,email',
                'role' => 'required|in:owner,admin,reviewer,viewer'
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
        if (!$request->user()->canManageCollaborators($project)) {
            abort(403);
        }

        // Prevent removing the owner
        if ($project->created_by === $user->id) {
            abort(403, 'Cannot remove the project owner');
        }

        // Notify project members about the member removal
        $this->notifyProjectMembers(
            $project,
            new MemberRemoved($project->name, 'project', $user->name, $request->user()),
            [$request->user()->id, $user->id] // Exclude the remover and the removed user
        );

        // Remove all project collaborator records for this user (both approved and pending)
        ProjectCollaborator::where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->delete();

        return redirect()->route('projects.share', $project)
            ->with('success', 'Member removed successfully');
    }

    public function removeCollaborator(Request $request, ProjectCollaborator $collaborator)
    {
        // Load the project relationship if not loaded
        $project = $collaborator->project()->first();
        
        if (!$project) {
            abort(404, 'Project not found');
        }
        
        // Only project owner can delete collaborators
        if (!$request->user()->canManageCollaborators($project)) {
            abort(403);
        }

        // Prevent deleting owner record
        if ($collaborator->role === 'owner' || $project->created_by === $collaborator->user_id) {
            abort(403, 'Cannot remove the project owner');
        }

        $collaborator->delete();

        return redirect()->route('projects.share', $project)
            ->with('success', 'Invitation deleted successfully');
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

        // Update notification with approval timestamp
        $notification = $user->notifications()
            ->where('data->project_id', $project->id)
            ->where('data->type', 'project_invitation_pending')
            ->first();
        if ($notification) {
            $notification->approve_at = now();
            $notification->markAsRead();
        }

        // Send notification to the project owner about the acceptance
        \Log::info('Sending project acceptance notification to owner: ' . $project->owner->id);
        try {
            $project->owner->notify(new ProjectInvitationResponseNotification($project, $user, 'accepted', $project->owner));
            \Log::info('Project acceptance notification sent successfully');
        } catch (\Exception $e) {
            \Log::error('Failed to send project acceptance notification: ' . $e->getMessage());
        }

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

        // Update notification with rejection timestamp
        $notification->sent_at = now();
        $notification->markAsRead();

        // Send notification to the project owner about the decline
        $project = Project::findOrFail($projectId);
        \Log::info('Sending project decline notification to owner: ' . $project->owner->id);
        try {
            $project->owner->notify(new ProjectInvitationResponseNotification($project, $user, 'declined', $project->owner));
            \Log::info('Project decline notification sent successfully');
        } catch (\Exception $e) {
            \Log::error('Failed to send project decline notification: ' . $e->getMessage());
        }

        return back()->with('success', 'You have declined the project invitation.');
    }

    public function archive(Request $request, Project $project)
    {
        $user = $request->user();

        // Check if user can archive (only workspace admins can archive)
        if (!$user->canArchiveProject($project)) {
            abort(403, 'You do not have permission to archive this project');
        }

        // Update project status to archived
        $project->status = 'archived';
        $project->save();

        // Notify project members about the archiving
        $this->notifyProjectMembers(
            $project,
            new ProjectArchived($project, $user, true),
            $user->id
        );

        // Redirect back to workspace page
        if ($project->workspace_id) {
            return redirect()->route('workspaces.show', $project->workspace)
                ->with('success', 'Project archived successfully.');
        }

        return redirect()->route('archive.index')
            ->with('success', 'Project archived successfully.');
    }

    public function unarchive(Request $request, Project $project)
    {
        $user = $request->user();

        // Check if user can unarchive (only workspace admins can unarchive)
        if (!$user->canArchiveProject($project)) {
            abort(403, 'You do not have permission to unarchive this project');
        }

        // Update project status back to active
        $project->status = 'active';
        $project->save();

        // Notify project members about the unarchiving
        $this->notifyProjectMembers(
            $project,
            new ProjectArchived($project, $user, false),
            [$user->id]
        );

        // Redirect to workspace archive if project belongs to a workspace
        if ($project->workspace_id) {
            return redirect()->route('workspace.archive', $project->workspace)
                ->with('success', 'Project unarchived successfully.');
        }

        return redirect()->route('archive.index')
            ->with('success', 'Project unarchived successfully.');
    }

    /**
     * Bulk invite via CSV upload for project.
     */
    public function bulkInvite(Request $request, Project $project)
    {
        if (!$request->user()->canManageCollaborators($project)) {
            abort(403);
        }

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
            'role' => 'required|in:owner,admin,reviewer,viewer'
        ]);

        $file = $request->file('csv_file');
        $defaultRole = $request->input('role');

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
            if (!in_array($role, ['owner', 'admin', 'reviewer', 'viewer'])) {
                $role = $defaultRole;
            }

            $user = User::where('email', $email)->first();

            if ($user) {
                // Check if user is already a member
                $existingMembership = ProjectCollaborator::where('project_id', $project->id)
                    ->where('user_id', $user->id)
                    ->where('status', 'approved')
                    ->first();

                if ($existingMembership) {
                    $skippedCount++;
                    continue;
                }

                // Check if there's already a pending request
                $existingPendingRequest = ProjectCollaborator::where('project_id', $project->id)
                    ->where('user_id', $user->id)
                    ->where('status', 'pending')
                    ->first();

                if ($existingPendingRequest) {
                    $skippedCount++;
                    continue;
                }

                // Create project collaborator record
                $projectCollaborator = ProjectCollaborator::create([
                    'project_id' => $project->id,
                    'user_id' => $user->id,
                    'role' => $role,
                    'status' => 'pending',
                    'invited_at' => now()
                ]);

                // Send notification
                $user->notify(new ProjectAccessShare($project, $request->user(), 'pending'));
                $invitedCount++;
            } else {
                $skippedCount++;
            }
        }

        return redirect()->route('projects.share', $project)
            ->with('success', "Bulk invite completed: {$invitedCount} invitations sent, {$skippedCount} skipped (users not found or already invited)");
    }

    /**
     * Notify project members about an event (excluding specified users).
     */
    private function notifyProjectMembers(Project $project, $notification, array $excludeUserIds = []): void
    {
        try {
            $membersToNotify = collect();

            // Get project owner
            if ($project->creator && !in_array($project->creator->id, $excludeUserIds)) {
                $membersToNotify->push($project->creator);
            }

            // Get approved collaborators
            $collaborators = ProjectCollaborator::where('project_id', $project->id)
                ->where('status', 'approved')
                ->with('user')
                ->get();

            foreach ($collaborators as $collaborator) {
                if ($collaborator->user && !in_array($collaborator->user->id, $excludeUserIds)) {
                    $membersToNotify->push($collaborator->user);
                }
            }

            // Send notifications
            foreach ($membersToNotify->unique('id') as $user) {
                $user->notify($notification);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to notify project members', [
                'project_id' => $project->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}