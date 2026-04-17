<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Folder extends Model
{
    use LogsActivity;

    protected $fillable = ['project_id','parent_folder_id','name','order'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'project_id', 'parent_folder_id'])
            ->logOnlyDirty();
    }

    public function parent()
    {
        return $this->belongsTo(Folder::class,'parent_folder_id');
    }

    public function children()
    {
        return $this->hasMany(Folder::class,'parent_folder_id')->orderBy('order');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class)->orderBy('created_at', 'desc');
    }

    /**
     * Scope to get folders ordered by order column
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    /**
     * Scope to get folders by parent_id
     */
    public function scopeByParent($query, $parentId)
    {
        return $query->where('parent_folder_id', $parentId);
    }

    /**
     * Get the maximum order value for a given parent_id
     */
    public static function getMaxOrder($parentId = null): int
    {
        return self::where('parent_folder_id', $parentId)->max('order') ?? -1;
    }

    /**
     * Reorder all folders for a given parent_id to maintain sequential order (0,1,2,...)
     * This ensures no gaps in ordering after deletions
     */
    public static function reorderSiblings($parentId = null): void
    {
        $folders = self::where('parent_folder_id', $parentId)
            ->orderBy('order', 'asc')
            ->get();

        foreach ($folders as $index => $folder) {
            $folder->update(['order' => $index]);
        }
    }

    /**
     * Update order for multiple folders at once
     * Accepts an array of folder IDs in the desired order
     */
    public static function bulkUpdateOrder(array $folderIds, $parentId = null): void
    {
        \DB::transaction(function () use ($folderIds, $parentId) {
            // Normalize: treat 0 as null for root folders
            $normalizedParentId = ($parentId === 0) ? null : $parentId;

            foreach ($folderIds as $index => $folderId) {
                $folder = self::find($folderId);
                if ($folder) {
                    // Normalize folder's parent_folder_id
                    $folderParentId = ($folder->parent_folder_id === 0) ? null : $folder->parent_folder_id;

                    // Check if parent_folder_id matches
                    if ($folderParentId == $normalizedParentId) {
                        $folder->update(['order' => $index]);
                    }
                }
            }
        });
    }
}