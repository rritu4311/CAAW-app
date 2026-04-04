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
     */
    public function canUserUpload(User $user): bool
    {
        return $this->userHasRole($user, ['owner', 'admin', 'reviewer']);
    }

    /**
     * Check if user can comment on this project.
     */
    public function canUserComment(User $user): bool
    {
        return $this->userHasRole($user, ['owner', 'admin', 'reviewer']);
    }

    /**
     * Check if user can approve/reject assets in this project.
     */
    public function canUserApprove(User $user): bool
    {
        return $this->userHasRole($user, ['owner', 'admin', 'reviewer']);
    }

    /**
     * Check if user can manage collaborators (owner only).
     */
    public function canUserManageCollaborators(User $user): bool
    {
        return $this->isOwnedBy($user);
    }

    /**
     * Check if user can delete this project (owner only).
     */
    public function canUserDelete(User $user): bool
    {
        return $this->isOwnedBy($user);
    }
}
