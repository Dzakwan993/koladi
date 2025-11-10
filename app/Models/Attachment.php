<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Attachment extends Model
{
    use HasFactory;

    protected $table = 'attachments';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'attachable_type',
        'attachable_id',
        'file_url',
        'uploaded_by'
    ];

    protected $casts = [
        'uploaded_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = $model->id ?: Str::uuid()->toString();
        });
    }

    // Relasi polymorphic
    public function attachable()
    {
        return $this->morphTo();
    }

    // Relasi ke User (uploader)
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}