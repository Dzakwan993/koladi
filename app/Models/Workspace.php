<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Conversation; // ✅ Jangan lupa import
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Workspace extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'company_id',
        'type',
        'name',
        'description',
        'created_by'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = $model->id ?: Str::uuid()->toString();
        });

        // ✅ TAMBAHKAN: Event untuk auto-update conversation
        static::updated(function ($workspace) {
            if ($workspace->isDirty('name')) {
                $mainConversation = Conversation::where('workspace_id', $workspace->id)
                    ->where('type', 'group')
                    ->first();

                if ($mainConversation) {
                    $mainConversation->update(['name' => $workspace->name]);
                    Log::info("Auto-updated conversation name from '{$workspace->getOriginal('name')}' to '{$workspace->name}'");
                }
            }
        });

        // ✅ Opsional: Auto-create main conversation jika belum ada
        static::created(function ($workspace) {
            $existingConversation = Conversation::where('workspace_id', $workspace->id)
                ->where('type', 'group')
                ->first();

            if (!$existingConversation) {
                Conversation::create([
                    'workspace_id' => $workspace->id,
                    'type' => 'group',
                    'name' => $workspace->name,
                    'created_by' => $workspace->created_by,
                ]);
            }
        });
    }

    // Relasi ke Company
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    // Relasi ke User (creator)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relasi ke User_Workspaces
    public function userWorkspaces()
    {
        return $this->hasMany(UserWorkspace::class, 'workspace_id');
    }

    // Relasi many-to-many ke Users melalui User_Workspaces
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_workspaces', 'workspace_id', 'user_id')
            ->withPivot('roles_id', 'status_active')
            ->withTimestamps();
    }

    // ✅ TAMBAHKAN: Relasi ke main conversation
    public function mainConversation()
    {
        return $this->hasOne(Conversation::class, 'workspace_id')
            ->where('type', 'group');
    }

    // Scope untuk workspace aktif
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    // Scope berdasarkan type
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
