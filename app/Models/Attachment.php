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
    public $timestamps = false;

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
        'uploaded_at' => 'datetime',
    ];

    // 🔥 Tambahkan appends untuk accessor
    protected $appends = ['url', 'formatted_size', 'is_image', 'file_icon'];

    // Boot method untuk auto-generate UUID
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Generate UUID jika belum ada
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }

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

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // 🔥 Accessor untuk URL file
    public function getUrlAttribute()
    {
        // Jika file_url sudah full path (dimulai dengan http), return as is
        if (Str::startsWith($this->file_url, ['http://', 'https://'])) {
            return $this->file_url;
        }

        // Jika relative path, tambahkan storage URL
        return asset('storage/' . ltrim($this->file_url, '/'));
    }

    // 🔥 Accessor untuk format size
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

    // 🔥 Accessor untuk cek apakah file adalah gambar
    public function getIsImageAttribute()
    {
        if (!$this->file_type) return false;

        $imageMimes = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml'
        ];

        return in_array($this->file_type, $imageMimes);
    }

    // 🔥 Accessor untuk icon file
    public function getFileIconAttribute()
    {
        if ($this->is_image) {
            return '🖼️';
        }

        if (!$this->file_name && !$this->file_url) {
            return '📎';
        }

        // Get extension from file_name or file_url
        $fileName = $this->file_name ?? $this->file_url;
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        return match ($ext) {
            'pdf' => '📄',
            'doc', 'docx' => '📝',
            'xls', 'xlsx' => '📊',
            'zip', 'rar' => '📦',
            'mp4', 'avi', 'mov' => '🎥',
            'mp3', 'wav' => '🎵',
            default => '📎'
        };
    }


    // Di App\Models\Attachment.php

// 🔥 Accessor untuk nama file
public function getFileNameAttribute()
{
    // Jika file_name sudah ada, gunakan itu
    if ($this->attributes['file_name'] ?? null) {
        return $this->attributes['file_name'];
    }
    
    // Jika tidak, ekstrak dari file_url
    if ($this->file_url) {
        return basename($this->file_url);
    }
    
    return 'unknown';
}

// 🔥 Accessor untuk file extension
public function getFileExtensionAttribute()
{
    $fileName = $this->file_name;
    return pathinfo($fileName, PATHINFO_EXTENSION);
}

// 🔥 Accessor untuk tipe file yang lebih spesifik
public function getFileTypeAttribute()
{
    if ($this->is_image) {
        return 'image';
    }
    
    $extension = strtolower($this->file_extension);
    
    $documentTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'];
    $archiveTypes = ['zip', 'rar', '7z'];
    $codeTypes = ['js', 'php', 'html', 'css', 'json', 'xml'];
    
    if (in_array($extension, $documentTypes)) {
        return 'document';
    } elseif (in_array($extension, $archiveTypes)) {
        return 'archive';
    } elseif (in_array($extension, $codeTypes)) {
        return 'code';
    }
    
    return 'other';
}
}
