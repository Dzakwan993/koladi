<?php

namespace App\Models;

use App\Models\Comment;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class File extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'files';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'folder_id',
        'company_id',    
        'workspace_id',
        'file_url',
        'file_name',
        'file_path',
        'file_size',
        'file_type',
        'is_private',
        'uploaded_by',
        'uploaded_at',
    ];

    protected $casts = [
        'is_private' => 'boolean',
        'uploaded_at' => 'datetime'
    ];

    // Accessor untuk formatted data
    protected $appends = ['formatted_size', 'file_icon', 'type_label'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }

            if (empty($model->uploaded_at)) {
                $model->uploaded_at = now();
            }
        });
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Relasi ke Folder
     */
    public function folder()
    {
        return $this->belongsTo(Folder::class, 'folder_id');
    }

    /**
     * Relasi ke Workspace
     */
    public function workspace()
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }

    /**
     * Relasi ke User (uploader)
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Relasi ke DocumentRecipient
     */
    public function documentRecipients()
    {
        return $this->hasMany(DocumentRecipient::class, 'document_id', 'id');
    }

    /**
     * 🔥 Relasi ke Comment (polymorphic)
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')
            ->whereNull('parent_comment_id')
            ->with(['user', 'replies.user', 'replies.replies.user'])
            ->orderBy('created_at', 'desc');
    }

    /**
     * 🔥 Relasi ke Attachment
     */
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable')->latest();
    }

    // ========================================
    // ACCESSORS & HELPERS
    // ========================================

    /**
     * Get formatted file size
     */
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

    /**
     * Get file icon based on type
     */
    public function getFileIconAttribute()
    {
        if (!$this->file_type && !$this->file_name) {
            return asset('images/icons/file-unknown.svg');
        }

        $type = $this->getFileTypeCategory();

        $iconMap = [
            'PDF' => 'pdf.svg',
            'Word' => 'microsoft-word.svg',
            'Excel' => 'excel.svg',
            'PowerPoint' => 'powerpoint.svg',
            'Image' => 'image.svg',
            'Video' => 'video.svg',
            'Audio' => 'audio.svg',
            'Text' => 'text-file.svg',
            'Zip' => 'zip.svg',
            'Code' => 'code.svg',
        ];

        $icon = $iconMap[$type] ?? 'file-unknown.svg';
        return asset('images/icons/' . $icon);
    }

    /**
     * Get human-readable file type label
     */
    public function getTypeLabelAttribute()
    {
        return $this->getFileTypeCategory();
    }

    /**
     * Determine file type category
     */
    public function getFileTypeCategory()
    {
        if (!$this->file_type && !$this->file_name) {
            return 'Unknown';
        }

        // Check by MIME type first
        if ($this->file_type) {
            if (str_contains($this->file_type, 'pdf')) return 'PDF';
            if (str_contains($this->file_type, 'word') || str_contains($this->file_type, 'document')) return 'Word';
            if (str_contains($this->file_type, 'excel') || str_contains($this->file_type, 'spreadsheet')) return 'Excel';
            if (str_contains($this->file_type, 'powerpoint') || str_contains($this->file_type, 'presentation')) return 'PowerPoint';
            if (str_contains($this->file_type, 'image/')) return 'Image';
            if (str_contains($this->file_type, 'video/')) return 'Video';
            if (str_contains($this->file_type, 'audio/')) return 'Audio';
            if (str_contains($this->file_type, 'text/')) return 'Text';
            if (str_contains($this->file_type, 'zip') || str_contains($this->file_type, 'compressed')) return 'Zip';
        }

        // Fallback to file extension
        if ($this->file_name) {
            $ext = strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));

            if ($ext === 'pdf') return 'PDF';
            if (in_array($ext, ['doc', 'docx'])) return 'Word';
            if (in_array($ext, ['xls', 'xlsx'])) return 'Excel';
            if (in_array($ext, ['ppt', 'pptx'])) return 'PowerPoint';
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) return 'Image';
            if (in_array($ext, ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv'])) return 'Video';
            if (in_array($ext, ['mp3', 'wav', 'ogg', 'flac'])) return 'Audio';
            if (in_array($ext, ['txt', 'md', 'log'])) return 'Text';
            if (in_array($ext, ['zip', 'rar', '7z', 'tar', 'gz'])) return 'Zip';
            if (in_array($ext, ['js', 'php', 'html', 'css', 'py', 'java', 'cpp', 'c'])) return 'Code';
        }

        return 'Unknown';
    }

    /**
     * Check if file is an image
     */
    public function isImage()
    {
        return $this->getFileTypeCategory() === 'Image';
    }

    /**
     * Check if file is a video
     */
    public function isVideo()
    {
        return $this->getFileTypeCategory() === 'Video';
    }

    /**
     * Get full file URL
     */
    public function getFileUrl()
    {
        if (str_starts_with($this->file_url, ['http://', 'https://'])) {
            return $this->file_url;
        }

        return asset('storage/' . ltrim($this->file_url, '/'));
    }

    /**
     * Get file download path
     */
    public function getDownloadPath()
    {
        return storage_path('app/public/' . ltrim($this->file_path ?? $this->file_url, '/'));
    }

    // Relasi ke Company
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
