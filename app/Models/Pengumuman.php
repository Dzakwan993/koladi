<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Pengumuman extends Model
{
    use HasUuids;

    protected $table = 'announcements'; // tabel utama
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
    'company_id',
    'workspace_id',
    'created_by',
    'title',
    'description',
    'due_date',
    'auto_due',
    'is_private',
];

    /**
     * 🔹 Relasi ke pembuat pengumuman
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 🔹 Relasi ke workspace - PERBAIKI INI
     */
    public function workspace()
    {
        return $this->belongsTo(Workspace::class, 'workspace_id'); // ✅ Workspace, bukan UserWorkspace
    }

    /**
     * 🔹 Relasi ke penerima (anggota yang bisa lihat jika private)
     */
    public function recipients()
    {
        return $this->belongsToMany(
            User::class,
            'announcement_recipients', // tabel pivot
            'announcement_id',         // FK ke announcements
            'user_id'                  // FK ke users
        );
    }

    /**
     * 🔹 Cek apakah pengumuman ini bisa dilihat oleh user tertentu
     */
    public function isVisibleTo($user)
    {
        if (!$user)
            return false;

        // 🔸 1. Pembuat pengumuman otomatis bisa lihat
        if ($this->created_by === $user->id) {
            return true;
        }

        // 🔸 2. Kalau pengumuman tidak private -> semua bisa lihat
        if (!$this->is_private) {
            return true;
        }

        // 🔸 3. Kalau private -> cek apakah user termasuk penerima
        return $this->recipients()->where('users.id', $user->id)->exists();
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->latest();
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable')->latest();
    }

    public function company()
{
    return $this->belongsTo(Company::class, 'company_id');
}




}

