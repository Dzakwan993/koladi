<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Attachment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'attachable_type',
        'attachable_id',
        'file_url',
        'file_name',
        'file_size',
        'file_type',
        'uploaded_by'
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    // Relasi polymorphic
    public function attachable()
    {
        return $this->morphTo();
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Helper untuk mendapatkan URL file
    public function getUrlAttribute()
    {
        // Jika file_url sudah full path (dimulai dengan http), return as is
        if (str_starts_with($this->file_url, 'http')) {
            return $this->file_url;
        }

        // Jika relative path, tambahkan asset URL
        return url('storage/' . $this->file_url);
    }

    // Helper untuk format size
    public function getFormattedSizeAttribute()
    {
        $bytes = $this->file_size;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}
