<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWorkspace extends Model
{
    protected $table = 'user_workspaces';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
