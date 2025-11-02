<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Color extends Model
{
    use HasFactory;

    protected $table = 'colors';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'rgb'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = $model->id ?: Str::uuid()->toString();
        });
    }

    // Relasi ke Labels
    public function labels()
    {
        return $this->hasMany(Label::class, 'color_id');
    }
}