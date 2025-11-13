<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model

{
    protected $fillables = [
        'id',
        'attachable_type',
        'attachable_id',
        'file_url',
        'uploaded_by',
        'uploaded_at',
    ];

    public $timestamps = false;

    //relasi
    public function attachable()
    {
        return $this->morphTo();
    }
}



