<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    protected $table = 'comments';

    protected $fillable = [
        'annotation_id',
        'asset_id',
        'user_id',
        'text',
        'mentioned_users',
    ];

    protected $casts = [
        'mentioned_users' => 'array',
    ];

    public function annotation(): BelongsTo
    {
        return $this->belongsTo(Annotation::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isImageComment(): bool
    {
        return $this->annotation_id !== null;
    }

    public function isAssetComment(): bool
    {
        return $this->annotation_id === null;
    }
}
