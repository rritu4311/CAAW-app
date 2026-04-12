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
        
        // Get statistics for the dashboard
        $stats = [
            'total_projects' => $this->getUserProjectsCount($user),
            'total_assets' => $this->getUserAssetsCount($user),
            'pending_approvals' => $this->getPendingApprovalsCount($user),
            'approved_assets' => $this->getApprovedAssetsCount($user),
        ];
        
        // Get recent projects the user has access to
        $recentProjects = $this->getRecentProjects($user);
        
        // Get assets pending approval that the user can action
        $pendingApprovals = $this->getPendingApprovals($user);
        
        // Get recent activity (recently uploaded assets)
        $recentAssets = $this->getRecentAssets($user);
        
        return view('dashboard', compact(
            'stats',
            'recentProjects',
            'pendingApprovals',
            'recentAssets'
        ));
    }
    
    /**
     * Get the count of projects the user has access to.
     */
    private function getUserProjectsCount($user): int
    {
        // Count projects where user is owner
        $ownedProjects = Project::where('created_by', $user->id)->count();
        
        // Count projects where user is a direct collaborator
        $collaboratedProjects = $user->projects()
            ->where('project_collaborators.status', 'approved')
            ->count();
        
        return $ownedProjects + $collaboratedProjects;
    }
    
    /**
     * Get the count of assets the user has access to.
     */
    private function getUserAssetsCount($user): int
    {
        // Get all projects the user can view
        $projectIds = $this->getViewableProjectIds($user);
        
        return Asset::whereIn('project_id', $projectIds)->count();
    }
    
    /**
     * Get the count of pending approvals the user can action.
     */
    private function getPendingApprovalsCount($user): int
    {
        $projectIds = $this->getViewableProjectIds($user);
        
        return Asset::whereIn('project_id', $projectIds)
            ->where('status', 'in_review')
            ->count();
    }
    
    /**
     * Get the count of approved assets.
     */
    private function getApprovedAssetsCount($user): int
    {
        $projectIds = $this->getViewableProjectIds($user);
        
        return Asset::whereIn('project_id', $projectIds)
            ->where('status', 'approved')
            ->count();
    }
    
    /**
     * Get recent projects the user has access to.
     */
    private function getRecentProjects($user)
    {
        $projectIds = $this->getViewableProjectIds($user);
        
        return Project::whereIn('id', $projectIds)
            ->with('workspace')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }
    
    /**
     * Get assets pending approval that the user can action.
     */
    private function getPendingApprovals($user)
    {
        $projectIds = $this->getViewableProjectIds($user);
        
        return Asset::whereIn('project_id', $projectIds)
            ->where('status', 'in_review')
            ->with('project', 'uploadedBy')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }
    
    /**
     * Get recent assets uploaded by or accessible to the user.
     */
    private function getRecentAssets($user)
    {
        $projectIds = $this->getViewableProjectIds($user);
        
        return Asset::whereIn('project_id', $projectIds)
            ->with('project', 'uploadedBy')
            ->orderBy('created_at', 'desc')
            ->limit(10)
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
}
