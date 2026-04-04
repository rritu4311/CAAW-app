<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'theme',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function workspaces(): BelongsToMany
    {
        return $this->belongsToMany(Workspace::class, 'workspace_users')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get all projects associated with this user (as collaborator or owner).
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_collaborators')
            ->withPivot('role', 'status')
            ->withTimestamps();
    }

    /**
     * Get the collaborator record for a specific project.
     */
    public function getProjectCollaborator(Project $project): ?ProjectCollaborator
    {
        return ProjectCollaborator::where('project_id', $project->id)
            ->where('user_id', $this->id)
            ->where('status', 'approved')
            ->first();
    }

    /**
     * Check if user is the owner (creator) of a project.
     */
    public function isProjectOwner(Project $project): bool
    {
        return $project->created_by === $this->id;
    }

    /**
     * Check if user has any role in a project (owner or collaborator).
     */
    public function hasProjectAccess(Project $project): bool
    {
        if ($this->isProjectOwner($project)) {
            return true;
        }

        $collaborator = $this->getProjectCollaborator($project);
        return $collaborator !== null;
    }

    /**
     * Get the role for a project. Returns 'owner' if project creator, otherwise collaborator role.
     */
    public function getProjectRole(Project $project): ?string
    {
        if ($this->isProjectOwner($project)) {
            return 'owner';
        }

        $collaborator = $this->getProjectCollaborator($project);
        return $collaborator?->role;
    }

    /**
     * Check if user is a workspace admin for the project's workspace.
     */
    public function isWorkspaceAdmin(Project $project): bool
    {
        return WorkspaceUser::where('workspace_id', $project->workspace_id)
            ->where('user_id', $this->id)
            ->where('status', 'approved')
            ->where('role', 'admin')
            ->exists();
    }

    /**
     * Check if user is a workspace owner for the project's workspace.
     */
    public function isWorkspaceOwner(Project $project): bool
    {
        return WorkspaceUser::where('workspace_id', $project->workspace_id)
            ->where('user_id', $this->id)
            ->where('status', 'approved')
            ->where('role', 'owner')
            ->exists();
    }

    /**
     * Check if user is an approved workspace member (any role: owner, admin, user, member).
     */
    public function isWorkspaceMember(Project $project): bool
    {
        return WorkspaceUser::where('workspace_id', $project->workspace_id)
            ->where('user_id', $this->id)
            ->where('status', 'approved')
            ->exists();
    }

    /**
     * Check if user can view the project (Owner, Admin, Reviewer, Viewer, or any Workspace Member).
     */
    public function canViewProject(Project $project): bool
    {
        if ($this->hasProjectAccess($project)) {
            return true;
        }

        // All approved workspace members can view projects in their workspace
        return $this->isWorkspaceMember($project);
    }

    /**
     * Check if user can upload assets (Owner, Admin, Reviewer).
     * Workspace admins have read-only access, so they cannot upload.
     */
    public function canUploadToProject(Project $project): bool
    {
        if ($this->isProjectOwner($project)) {
            return true;
        }

        // Workspace admins have read-only access, exclude them
        if ($this->isWorkspaceAdmin($project) || $this->isWorkspaceOwner($project)) {
            return false;
        }

        $collaborator = $this->getProjectCollaborator($project);
        return $collaborator !== null && in_array($collaborator->role, ['admin', 'reviewer']);
    }

    /**
     * Check if user can comment (Owner, Admin, Reviewer).
     * Workspace admins have read-only access, so they cannot comment.
     */
    public function canCommentOnProject(Project $project): bool
    {
        if ($this->isProjectOwner($project)) {
            return true;
        }

        // Workspace admins have read-only access, exclude them
        if ($this->isWorkspaceAdmin($project) || $this->isWorkspaceOwner($project)) {
            return false;
        }

        $collaborator = $this->getProjectCollaborator($project);
        return $collaborator !== null && in_array($collaborator->role, ['admin', 'reviewer']);
    }

    /**
     * Check if user can approve/reject assets (Owner, Admin, Reviewer).
     * Workspace admins have read-only access, so they cannot approve/reject.
     */
    public function canApproveInProject(Project $project): bool
    {
        if ($this->isProjectOwner($project)) {
            return true;
        }

        // Workspace admins have read-only access, exclude them
        if ($this->isWorkspaceAdmin($project) || $this->isWorkspaceOwner($project)) {
            return false;
        }

        $collaborator = $this->getProjectCollaborator($project);
        return $collaborator !== null && in_array($collaborator->role, ['admin', 'reviewer']);
    }

    /**
     * Check if user can manage collaborators (Owner only).
     */
    public function canManageCollaborators(Project $project): bool
    {
        return $this->isProjectOwner($project);
    }

    /**
     * Check if user can delete the project (Owner only).
     */
    public function canDeleteProject(Project $project): bool
    {
        return $this->isProjectOwner($project);
    }

    /**
     * Check if user can manage project settings (Owner only).
     */
    public function canManageProject(Project $project): bool
    {
        return $this->isProjectOwner($project);
    }
}
