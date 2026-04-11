<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetVersion extends Model
{
    protected $table = 'asset_versions';

    protected $fillable = [
        'asset_id',
        'version_number',
        'name',
        'file_path',
        'file_type',
        'file_size',
        'hash',
        'status',
        'uploaded_by',
        'notes',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'version_number' => 'float',
        'uploaded_by' => 'integer',
        'asset_id' => 'integer',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFormattedVersionAttribute(): string
    {
        return 'v' . number_format($this->version_number, 1);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    // Workflow state transition methods
    public function submitForReview(): bool
    {
        if ($this->status !== 'draft') {
            return false;
        }

        $this->status = 'in_review';
        $this->save();
        return true;
    }

    public function approve(): bool
    {
        if ($this->status !== 'in_review') {
            return false;
        }

        $this->status = 'approved';
        $this->save();
        return true;
    }

    public function reject(string $reason): bool
    {
        if ($this->status !== 'in_review') {
            return false;
        }

        $this->status = 'draft';
        $this->save();
        return true;
    }

    public function requestChanges(string $comments): bool
    {
        if ($this->status !== 'in_review') {
            return false;
        }

        $this->status = 'draft';
        $this->notes = $comments;
        $this->save();
        return true;
    }

    // Status helper methods
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isInReview(): bool
    {
        return $this->status === 'in_review';
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

    public function canBeSubmitted(): bool
    {
        return $this->status === 'draft';
    }

    public function canBeApproved(): bool
    {
        return $this->status === 'in_review';
    }
}
