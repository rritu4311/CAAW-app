<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workspace extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'owner_id',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workspace_users')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function workspaceUsers(): HasMany
    {
        return $this->hasMany(WorkspaceUser::class);
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    /**
     * Get the workspace user record for a specific user.
     */
    public function getWorkspaceUser(User $user): ?WorkspaceUser
    {
        return $this->workspaceUsers()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->first();
    }

    /**
     * Check if user has a specific role in this workspace.
     */
    public function userHasRole(User $user, array $roles): bool
    {
        // Owner has full access
        if (in_array('owner', $roles) && $this->isOwnedBy($user)) {
            return true;
        }

        $workspaceUser = $this->getWorkspaceUser($user);
        if (!$workspaceUser) {
            return false;
        }

        return in_array($workspaceUser->role, $roles);
    }

    /**
     * Check if user can manage workspace users (Owner or Admin).
     */
    public function canUserManageMembers(User $user): bool
    {
        return $this->userHasRole($user, ['owner', 'admin']);
    }

    /**
     * Check if user can create projects (Owner, Admin, or User).
     */
    public function canUserCreateProject(User $user): bool
    {
        return $this->userHasRole($user, ['owner', 'admin', 'user']);
    }

    /**
     * Check if user can manage projects (Owner or User have full access).
     * Admin: read-only access.
     */
    public function canUserManageProjects(User $user): bool
    {
        return $this->userHasRole($user, ['owner', 'user']);
    }

    /**
     * Check if user can view all projects (Owner, Admin, User - all have view access).
     */
    public function canUserViewProjects(User $user): bool
    {
        return $this->userHasRole($user, ['owner', 'admin', 'user']);
    }
}
