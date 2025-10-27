<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class File extends Model
{
    protected $fillable = [
    'id',
    'folder_id',
    'workspace_id',
    'file_url',
    'is_private',
    'uploaded_by',
    'uploaded_at',
];

}
