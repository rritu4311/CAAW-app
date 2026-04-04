<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'name',
        'client_name',
        'description',
        'status',
        'deadline',
        'created_by',
    ];

    protected $casts = [
        'deadline' => 'date',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->created_by === $user->id;
    }

    public function folders()
    {
        return $this->hasMany(Folder::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function projectCollaborators(): HasMany
    {
        return $this->hasMany(ProjectCollaborator::class);
    }

    public function pendingApprovalsCount(): int
    {
        return $this->assets()
            ->withCount(['approvals' => function ($query) {
                $query->where('status', 'pending');
            }])
            ->get()
            ->sum('approvals_count');
    }

    /**
     * Get the collaborator record for a specific user.
     */
    public function getCollaborator(User $user): ?ProjectCollaborator
    {
        return $this->projectCollaborators()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->first();
    }

    /**
     * Check if user has a specific role in this project.
     */
    public function userHasRole(User $user, array $roles): bool
    {
        if (in_array('owner', $roles) && $this->isOwnedBy($user)) {
            return true;
        }

        $collaborator = $this->getCollaborator($user);
        if (!$collaborator || !$collaborator->isApproved()) {
            return false;
        }

        return in_array($collaborator->role, $roles);
    }

    /**
     * Check if user can upload assets to this project.
     * Workspace User and Owner roles have full access.
     */
    public function canUserUpload(User $user): bool
    {
        // Project-level roles
        if ($this->userHasRole($user, ['owner', 'admin', 'reviewer'])) {
            return true;
        }

        // Check workspace role via database (workspace_id is always available)
        $workspaceUser = \App\Models\WorkspaceUser::where('workspace_id', $this->workspace_id)
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->first();

        if ($workspaceUser && in_array($workspaceUser->role, ['user', 'owner'])) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can comment on this project.
     * Workspace User role has full access like Owner.
     */
    public function canUserComment(User $user): bool
    {
        // Project-level roles
        if ($this->userHasRole($user, ['owner', 'admin', 'reviewer'])) {
            return true;
        }

        // Workspace User role has full access like Owner
        if ($user->hasWorkspaceRole($this->workspace, ['user'])) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can approve/reject assets in this project.
     * Workspace User role has full access like Owner.
     */
    public function canUserApprove(User $user): bool
    {
        // Project-level roles
        if ($this->userHasRole($user, ['owner', 'admin', 'reviewer'])) {
            return true;
        }

        // Workspace User role has full access like Owner
        if ($user->hasWorkspaceRole($this->workspace, ['user'])) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can manage collaborators (owner or workspace User).
     */
    public function canUserManageCollaborators(User $user): bool
    {
        return $this->isOwnedBy($user) || $user->hasWorkspaceRole($this->workspace, ['user']);
    }

    /**
     * Check if user can delete this project (owner or workspace User).
     */
    public function canUserDelete(User $user): bool
    {
        return $this->isOwnedBy($user) || $user->hasWorkspaceRole($this->workspace, ['user']);
    }
}
