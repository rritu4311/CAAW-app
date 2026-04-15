<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\AssetVersion;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Asset extends Model
{
    use LogsActivity;

    protected $table = 'assets';

    protected $fillable = [
        'project_id',
        'folder_id',
        'name',
        'file_path',
        'file_type',
        'file_size',
        'status',
        'uploaded_by',
        'version',
        'current_version_id'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'file_type', 'file_size', 'status', 'folder_id'])
            ->logOnlyDirty();
    }

    protected $casts = [
        'file_size' => 'integer',
        'folder_id' => 'integer',
        'uploaded_by' => 'integer',
        'project_id' => 'integer',
        'version' => 'float',
        'current_version_id' => 'integer',
    ];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
    
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class);
    }

    public function pendingApprovals(): HasMany
    {
        return $this->hasMany(Approval::class)->where('status', 'pending');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(AssetVersion::class)->orderBy('version_number', 'desc');
    }

    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(AssetVersion::class, 'current_version_id');
    }

    public function annotations(): HasMany
    {
        return $this->hasMany(Annotation::class)->orderBy('created_at', 'desc');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->whereNull('annotation_id')->orderBy('created_at', 'desc');
    }

    public function getFormattedVersionAttribute(): string
    {
        return 'v' . number_format($this->version, 1);
    }

    public function isImage(): bool
    {
        return $this->file_type === 'image';
    }

    public function isVideo(): bool
    {
        return $this->file_type === 'video';
    }

    public function isDocument(): bool
    {
        return in_array($this->file_type, ['pdf', 'doc']);
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

        $this->status = 'rejected';
        $this->save();
        return true;
    }

    public function requestChanges(string $comments): bool
    {
        if ($this->status !== 'in_review') {
            return false;
        }

        $this->status = 'draft';
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
        return $this->status === 'in_review' || $this->status === 'pending_approval';
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
        return $this->status === 'in_review' || $this->status === 'pending_approval';
    }
}
