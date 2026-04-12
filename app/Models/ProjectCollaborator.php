<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectCollaborator extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'role',
        'status',
        'invited_at',
        'approved_at',
    ];

    protected $casts = [
        'invited_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the project that owns this collaboration.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user that is part of this collaboration.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the collaboration has been approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the collaboration is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the collaboration has been rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if the user is an owner collaborator.
     */
    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    /**
     * Check if the user is an admin collaborator.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the user is a reviewer.
     */
    public function isReviewer(): bool
    {
        return $this->role === 'reviewer';
    }

    /**
     * Check if this collaborator can upload assets (only owner).
     * Admin, Reviewer, and Viewer have restricted access (read-only).
     */
    public function canUpload(): bool
    {
        return $this->role === 'owner' && $this->isApproved();
    }

    /**
     * Check if this collaborator can comment (admin, reviewer).
     */
    public function canComment(): bool
    {
        return in_array($this->role, ['admin', 'reviewer']) && $this->isApproved();
    }

    /**
     * Check if this collaborator can approve/reject (admin, reviewer).
     */
    public function canApprove(): bool
    {
        return in_array($this->role, ['admin', 'reviewer']) && $this->isApproved();
    }

    /**
     * Check if the user is a viewer.
     */
    public function isViewer(): bool
    {
        return $this->role === 'viewer';
    }
}
