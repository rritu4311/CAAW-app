<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'requested_by',
        'status',
        'decided_at',
        'decided_by',
        'decision_reason',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

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

    public function approve(User $decidedBy, string $reason = null): void
    {
        $this->update([
            'status' => 'approved',
            'decided_at' => now(),
            'decided_by' => $decidedBy->id,
            'decision_reason' => $reason,
        ]);
    }

    public function reject(User $decidedBy, string $reason = null): void
    {
        $this->update([
            'status' => 'rejected',
            'decided_at' => now(),
            'decided_by' => $decidedBy->id,
            'decision_reason' => $reason,
        ]);
    }
}
