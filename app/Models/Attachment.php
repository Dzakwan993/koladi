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

    // ✅ NONAKTIFKAN TIMESTAMPS
    public $timestamps = false;

    protected $fillable = [
        'id',
        'attachable_type',
        'attachable_id',
        'file_url',
        'uploaded_by',
        'uploaded_at'
        // ✅ HAPUS file_name, file_size, mime_type karena tidak ada di tabel
    ];

    protected $casts = [
        'uploaded_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = $model->id ?: Str::uuid()->toString();

            // Set uploaded_at jika belum diset
            if (empty($model->uploaded_at)) {
                $model->uploaded_at = now();
            }
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

    // ✅ ACCESSOR untuk file_name (dari file_url)
    public function getFileNameAttribute()
    {
        return basename($this->file_url);
    }

    // ✅ ACCESSOR untuk file_size (default null)
    public function getFileSizeAttribute()
    {
        return null;
    }

    // ✅ ACCESSOR untuk mime_type (default null)
    public function getMimeTypeAttribute()
    {
        return null;
    }
}
