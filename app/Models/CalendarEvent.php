<?php

// File: app/Models/CalendarEvent.php

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
        'created_by',
        'title',
        'description',
        'start_datetime',
        'end_datetime',
        'recurrence',
        'is_private',
        'is_online_meeting',
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
     * Get durasi event dalam menit
     */
    public function getDurationInMinutes()
    {
        return $this->start_datetime->diffInMinutes($this->end_datetime);
    }

    /**
     * Get formatted date range
     */
    public function getFormattedDateRange()
    {
        $start = $this->start_datetime->locale('id');
        $end = $this->end_datetime->locale('id');

        if ($start->isSameDay($end)) {
            return $start->format('l, d M Y') . ', ' .
                $start->format('h:i A') . ' - ' .
                $end->format('h:i A');
        }

        return $start->format('l, d M Y h:i A') . ' - ' .
            $end->format('l, d M Y h:i A');
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
