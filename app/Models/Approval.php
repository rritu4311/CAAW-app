<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Approval extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'workflow_id',
        'assigned_to',
        'status',
        'decision_reason',
        'decided_at',
        'decided_by',
        'order',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function decidedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    // Approval decision methods
    public function approve(int $userId): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->status = 'approved';
        $this->decided_at = now();
        $this->decided_by = $userId;
        $this->save();

        // Update asset status if all approvals are approved
        $this->updateAssetStatus();

        return true;
    }

    public function reject(int $userId, string $reason): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->status = 'rejected';
        $this->decision_reason = $reason;
        $this->decided_at = now();
        $this->decided_by = $userId;
        $this->save();

        // Revert asset to draft
        $this->asset->update(['status' => 'draft']);

        return true;
    }

    public function requestChanges(int $userId, string $comments): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->status = 'changes_requested';
        $this->decision_reason = $comments;
        $this->decided_at = now();
        $this->decided_by = $userId;
        $this->save();

        // Revert asset to draft but retain comments
        $this->asset->update(['status' => 'draft']);

        return true;
    }

    private function updateAssetStatus(): void
    {
        $asset = $this->asset;
        $pendingApprovals = $asset->pendingApprovals()->count();

        if ($pendingApprovals === 0) {
            // All approvals completed, check if all were approved
            $rejectedCount = $asset->approvals()->where('status', 'rejected')->count();
            $changesRequestedCount = $asset->approvals()->where('status', 'changes_requested')->count();

            if ($rejectedCount > 0 || $changesRequestedCount > 0) {
                $asset->update(['status' => 'draft']);
            } else {
                $asset->update(['status' => 'approved']);
            }
        }
    }

    // Status helper methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function hasChangesRequested(): bool
    {
        return $this->status === 'changes_requested';
    }

    public function isDecided(): bool
    {
        return in_array($this->status, ['approved', 'rejected', 'changes_requested']);
    }
}
