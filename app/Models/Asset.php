<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asset extends Model
{
    protected $fillable = [
        'filename',
        'original_name',
        'mime_type',
        'size',
        'path',
        'hash',
        'folder_id',
        'url',
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

    protected $casts = [
        'size' => 'integer',
        'file_size' => 'integer',
        'folder_id' => 'integer',
        'uploaded_by' => 'integer',
        'project_id' => 'integer',
    ];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    public function isDocument(): bool
    {
        return in_array($this->file_type, ['pdf', 'doc']);
    }

    public function isText(): bool
    {
        return str_starts_with($this->mime_type, 'text/') || $this->mime_type === 'text/markdown';
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size ?: $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
