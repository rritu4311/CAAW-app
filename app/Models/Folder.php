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
}