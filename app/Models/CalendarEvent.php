<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'calendar_events';

    protected $fillable = [
        'workspace_id',
        'company_id',
        'created_by',
        'title',
        'description',
        'start_datetime',
        'end_datetime',
        'recurrence',
        'is_private',
        'is_online_meeting',
        'location',
        'meeting_link',
        'deleted_at',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'is_private' => 'boolean',
        'is_online_meeting' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relasi ke workspace
     */
    public function workspace()
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relasi ke creator (user yang membuat event)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke participants
     */
    public function participants()
    {
        return $this->hasMany(CalendarParticipant::class, 'event_id');
    }

    /**
     * 🔹 TAMBAHAN: Relasi ke comments (polymorphic)
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->latest();
    }

    /**
     * 🔹 TAMBAHAN: Relasi ke attachments (polymorphic)
     */
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable')->latest();
    }

    /**
     * Get participants yang sudah accept
     */
    public function acceptedParticipants()
    {
        return $this->participants()->where('status', 'accepted');
    }

    /**
     * Get participants yang pending
     */
    public function pendingParticipants()
    {
        return $this->participants()->where('status', 'pending');
    }

    /**
     * Check apakah event sudah berlalu
     */
    public function isPast()
    {
        return $this->end_datetime < now();
    }

    /**
     * Check apakah event sedang berlangsung
     */
    public function isOngoing()
    {
        return $this->start_datetime <= now() && $this->end_datetime >= now();
    }

    /**
     * Check apakah event akan datang
     */
    public function isUpcoming()
    {
        return $this->start_datetime > now();
    }

    /**
     * Accessor untuk membersihkan kode [W-xxx] dari judul event
     */
    public function getTitleAttribute($value)
    {
        return preg_replace('/^\[W-[a-zA-Z0-9\-]+\]\s*/', '', (string)$value);
    }

    /**
     * Get durasi event dalam menit
     */
    public function getDurationInMinutes()
    {
        return $this->start_datetime->diffInMinutes($this->end_datetime);
    }

    /**
     * Get formatted date range (Bahasa Indonesia)
     */
    public function getFormattedDateRange()
    {
        $start = $this->start_datetime->locale('id');
        $end = $this->end_datetime->locale('id');

        if ($start->isSameDay($end)) {
            return $start->translatedFormat('l, d M Y') . ', ' .
                $start->format('H:i') . ' - ' .
                $end->format('H:i') . ' WIB';
        }

        return $start->translatedFormat('l, d M Y, H:i') . ' - ' .
            $end->translatedFormat('l, d M Y, H:i') . ' WIB';
    }

    /**
     * Scope untuk filter by workspace
     */
    public function scopeByWorkspace($query, $workspaceId)
    {
        return $query->where('workspace_id', $workspaceId);
    }

    /**
     * Scope untuk event yang tidak dihapus
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Scope untuk event yang akan datang
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_datetime', '>', now());
    }

    /**
     * Scope untuk event hari ini
     */
    public function scopeToday($query)
    {
        return $query->whereDate('start_datetime', today());
    }
}
