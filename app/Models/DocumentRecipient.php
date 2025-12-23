<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

use Illuminate\Database\Eloquent\Model;

class DocumentRecipient extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'document_id',
        'user_id',
        'status', // <- tambahkan status
    ];

    // ✅ RELASI KE USER
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
