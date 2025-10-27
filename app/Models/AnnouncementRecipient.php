<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AnnouncementRecipient extends Model
{
    use HasUuids;

    protected $table = 'announcement_recipients';

    protected $fillable = [
        'announcement_id',
        'user_id',
    ];
}
