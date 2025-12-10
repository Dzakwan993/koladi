<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'email',
        'address',
        'phone',
        'status',
        'trial_start',
        'trial_end',
    ];

    protected $casts = [
        'trial_start' => 'datetime',
        'trial_end' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = $model->id ?: Str::uuid()->toString();
        });
    }

    // Relationships
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_companies', 'company_id', 'user_id')
            ->withPivot('roles_id')
            ->withTimestamps();
    }

    public function userCompanies()
{
    return $this->hasMany(UserCompany::class, 'company_id');
}


    public function subscription()
    {
        return $this->hasOne(Subscription::class)->latest();
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    // 🔥 Helper: Cek apakah masih trial AKTIF
    public function isOnTrial()
    {
        if ($this->status !== 'trial' || !$this->trial_end) {
            return false;
        }

        return Carbon::parse($this->trial_end)->isFuture();
    }

    // 🔥 Helper: Berapa hari trial tersisa
    public function getTrialDaysRemainingAttribute()
    {
        if (!$this->trial_end) return 0;

        $trialEnds = Carbon::parse($this->trial_end);
        $now = Carbon::now();

        if ($trialEnds->isPast()) return 0;

        // ✅ UBAH INI: Tambahkan ceil() untuk pembulatan ke atas
        return (int) ceil($now->diffInDays($trialEnds, false));
    }

    // 🔥 Helper: Cek apakah punya subscription aktif
    public function hasActiveSubscription()
    {
        if (!$this->subscription) return false;

        return $this->subscription->status === 'active' &&
            Carbon::parse($this->subscription->end_date)->isFuture();
    }

    // 🔥 Helper: Cek apakah company bisa diakses (trial aktif ATAU subscription aktif)
    public function canAccess()
    {
        return $this->isOnTrial() || $this->hasActiveSubscription();
    }

    // 🔥 Helper: Total user yang bisa diundang
    public function getUserLimitAttribute()
    {
        if ($this->isOnTrial()) {
            return 999; // Unlimited selama trial
        }

        if ($this->subscription) {
            return $this->subscription->total_user_limit;
        }

        return 0;
    }

    // 🔥 Helper: Cek apakah masih bisa undang user
    public function canInviteMoreUsers()
    {
        $currentUserCount = $this->users()->count();
        $limit = $this->getUserLimitAttribute();

        return $currentUserCount < $limit;
    }

    // 🔥 Helper: Format trial status untuk UI
    public function getTrialStatusAttribute()
    {
        if (!$this->isOnTrial()) {
            return null;
        }

        $daysRemaining = $this->trial_days_remaining;

        if ($daysRemaining > 1) {
            return "Trial berakhir dalam {$daysRemaining} hari";
        } elseif ($daysRemaining == 1) {
            return "Trial berakhir besok";
        } else {
            return "Trial berakhir hari ini";
        }
    }
}
