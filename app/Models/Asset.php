<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
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
}
