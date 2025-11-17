<?php

// File: app/Models/CalendarParticipant.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarParticipant extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'calendar_participants';

    protected $fillable = [
        'event_id',
        'user_id',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke event
     */
    public function event()
    {
        return $this->belongsTo(CalendarEvent::class, 'event_id');
    }

    /**
     * Relasi ke user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Check apakah participant sudah accept
     */
    public function hasAccepted()
    {
        return $this->status === 'accepted';
    }

    /**
     * Check apakah participant sudah decline
     */
    public function hasDeclined()
    {
        return $this->status === 'declined';
    }

    /**
     * Check apakah participant masih pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Scope untuk filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk accepted participants
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope untuk pending participants
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope untuk declined participants
     */
    public function scopeDeclined($query)
    {
        return $query->where('status', 'declined');
    }
}
