<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CalendarParticipant extends Model
{
    use HasFactory;

    protected $table = 'calendar_participants';

    public $incrementing = false;
    protected $keyType = 'string';

    // ✅ PENTING: Nonaktifkan timestamps
    public $timestamps = false;

    protected $fillable = [
        'id',
        'event_id',
        'user_id',
        'status'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = $model->id ?: Str::uuid()->toString();
        });
    }

    public function event()
    {
        return $this->belongsTo(CalendarEvent::class, 'event_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
