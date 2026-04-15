<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Asset;
use App\Models\Project;
use App\Models\Approval;

class DashboardController extends Controller
{
    /**
     * Display the user's dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        // Get quick stats
        $stats = [
            'pending_approvals' => $this->getPendingApprovalsCount($user),
            'assets_in_review' => $this->getAssetsInReviewCount($user),
            'new_comments' => $this->getNewComments($user)->count(),
        ];

        // Get recent activity from activity log
        $recentActivities = $user->recentActivities(10);

        // Get projects with latest asset thumbnails
        $projects = $this->getProjectsWithLatestAsset($user);

        // Get new comments with annotations
        $newComments = $this->getNewComments($user);

        return view('dashboard', compact(
            'stats',
            'recentActivities',
            'projects',
            'newComments'
        ));
    }

    /**
     * Get count of pending approvals (workspace + project requests).
     */
    private function getPendingApprovalsCount($user): int
    {
        // Count pending workspace requests
        $pendingWorkspaceCount = \App\Models\WorkspaceUser::whereIn('workspace_id', function($query) use ($user) {
            $query->select('id')
                ->from('workspaces')
                ->where('owner_id', $user->id)
                ->orWhereIn('id', function($q) use ($user) {
                    $q->select('workspace_id')
                        ->from('workspace_users')
                        ->where('user_id', $user->id)
                        ->where('status', 'approved')
                        ->whereIn('role', ['owner', 'admin']);
                });
        })
        ->where('status', 'pending')
        ->count();

        // Count pending project collaborator requests
        $pendingProjectCount = \App\Models\ProjectCollaborator::whereIn('project_id', function($query) use ($user) {
            $query->select('id')
                ->from('projects')
                ->where('created_by', $user->id)
                ->orWhereIn('workspace_id', function($q) use ($user) {
                    $q->select('workspace_id')
                        ->from('workspace_users')
                        ->where('user_id', $user->id)
                        ->where('status', 'approved')
                        ->whereIn('role', ['owner', 'admin']);
                });
        })
        ->where('status', 'pending')
        ->count();

        return $pendingWorkspaceCount + $pendingProjectCount;
    }

    /**
     * Get count of assets in review.
     */
    private function getAssetsInReviewCount($user): int
    {
        $projectIds = $this->getViewableProjectIds($user);
        return Asset::whereIn('project_id', $projectIds)
            ->where('status', 'in_review')
            ->where('uploaded_by', $user->id)
            ->count();
    }

    /**
     * Get new comments with annotations.
     */
    private function getNewComments($user)
    {
        $projectIds = $this->getViewableProjectIds($user);
        
        return \App\Models\Comment::whereIn('asset_id', function($query) use ($projectIds) {
            $query->select('id')->from('assets')->whereIn('project_id', $projectIds);
        })
        ->where(function($query) use ($user) {
            // Comments on assets owned by the user
            $query->whereHas('asset', function($q) use ($user) {
                $q->where('uploaded_by', $user->id);
            })
            // Or comments where the user is mentioned
            ->orWhereJsonContains('mentioned_users', $user->id);
        })
        ->where('user_id', '!=', $user->id) // Exclude comments made by the user themselves
        ->with(['user', 'asset.project.workspace', 'annotation'])
        ->orderBy('created_at', 'desc')
        ->limit(20)
        ->get();
    }

    /**
     * Get projects with their latest asset thumbnail.
     */
    private function getProjectsWithLatestAsset($user)
    {
        $projectIds = $this->getViewableProjectIds($user);

        return Project::whereIn('id', $projectIds)
            ->with(['workspace', 'assets' => function($query) {
                $query->latest()->limit(1);
            }])
            ->where('created_by', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get all project IDs the user can view.
     */
    private function getViewableProjectIds($user): array
    {
        // Projects owned by user
        $ownedProjectIds = Project::where('created_by', $user->id)->pluck('id')->toArray();

        // Projects where user is a direct collaborator
        $collaboratedProjectIds = $user->projects()
            ->where('project_collaborators.status', 'approved')
            ->pluck('projects.id')
            ->toArray();

        return array_unique(array_merge($ownedProjectIds, $collaboratedProjectIds));
    }

    /**
     * Show pending approvals for workspaces and projects.
     */
    public function pendingApprovals()
    {
        $user = Auth::user();

        // Get pending workspace requests (where user is owner/admin)
        $pendingWorkspaceUsers = \App\Models\WorkspaceUser::whereIn('workspace_id', function($query) use ($user) {
            $query->select('id')
                ->from('workspaces')
                ->where('owner_id', $user->id)
                ->orWhereIn('id', function($q) use ($user) {
                    $q->select('workspace_id')
                        ->from('workspace_users')
                        ->where('user_id', $user->id)
                        ->where('status', 'approved')
                        ->whereIn('role', ['owner', 'admin']);
                });
        })
        ->where('status', 'pending')
        ->with(['workspace', 'user'])
        ->get();

        // Get pending project collaborator requests (where user is owner/admin)
        $pendingProjectCollaborators = \App\Models\ProjectCollaborator::whereIn('project_id', function($query) use ($user) {
            $query->select('id')
                ->from('projects')
                ->where('created_by', $user->id)
                ->orWhereIn('workspace_id', function($q) use ($user) {
                    $q->select('workspace_id')
                        ->from('workspace_users')
                        ->where('user_id', $user->id)
                        ->where('status', 'approved')
                        ->whereIn('role', ['owner', 'admin']);
                });
        })
        ->where('status', 'pending')
        ->with(['project', 'user'])
        ->get();

        return view('pending-approvals', compact('pendingWorkspaceUsers', 'pendingProjectCollaborators'));
    }

    /**
     * Show new comments for workspaces and projects.
     */
    public function comments()
    {
        $user = Auth::user();

        $newComments = $this->getNewComments($user);

        return view('dashboard-comments', compact('newComments'));
    }
}
