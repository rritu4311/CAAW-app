<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Annotation extends Model
{
    protected $table = 'annotations';

    protected $fillable = [
        'asset_id',
        'x',
        'y',
        'width',
        'height',
        'status',
        'created_by',
    ];

    protected $casts = [
        'x' => 'decimal:2',
        'y' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->orderBy('created_at', 'asc');
    }

    // Status helper methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isAcknowledged(): bool
    {
        return $this->status === 'acknowledged';
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    public function acknowledge(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->status = 'acknowledged';
        $this->save();
        return true;
    }

    public function resolve(): bool
    {
        $this->status = 'resolved';
        $this->save();
        return true;
    }

    public function reopen(): bool
    {
        $this->status = 'pending';
        $this->save();
        return true;
    }
}
