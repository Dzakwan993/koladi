<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengumuman;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PengumumanController extends Controller
{
    /**
     * 🔹 Menampilkan daftar pengumuman berdasarkan user ID saja
     */
   public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        // Ambil semua pengumuman (urut terbaru)
        $pengumumans = Pengumuman::with(['recipients', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($p) use ($user) {
                // Properti bantu untuk view: apakah user boleh melihat isi?
                $p->boleh_lihat = $p->isVisibleTo($user);
                return $p;
            });

        return view('pengumuman', compact('pengumumans', 'user'));
    }


    /**
     * 🔹 Menyimpan pengumuman baru
     */
    public function store(Request $request)
{
    $request->validate([
        'title' => 'required|string',
        'description' => 'required|string',
        'due_date' => 'nullable|date',
        'is_private' => 'nullable|boolean',
        'auto_due' => 'nullable|string',
        'recipients' => 'nullable|array',
    ]);

    $user = Auth::user();
    if (!$user) {
        return redirect()->back()->with('error', 'User belum login!');
    }

    DB::beginTransaction();

    try {
        $pengumuman = new Pengumuman();
        $pengumuman->id = Str::uuid();
        $pengumuman->title = $request->title;
        $pengumuman->description = $request->description;
        $pengumuman->is_private = $request->has('is_private');
        $pengumuman->created_by = $user->id;

        // 🔹 Logika auto due (dengan default 1 hari dari sekarang)
        $autoDue = strtolower($request->auto_due ?? '1 hari dari sekarang');
        switch ($autoDue) {
            case '1 hari dari sekarang':
                $days = 1;
                break;
            case '3 hari dari sekarang':
                $days = 3;
                break;
            case '7 hari dari sekarang':
                $days = 7;
                break;
            default:
                // jika user isi manual atau tidak valid, tetap default 1 hari
                $days = 1;
                break;
        }

        // Tentukan tanggal jatuh tempo
        $tanggal = Carbon::now()->addDays($days)->toDateString();
        $pengumuman->auto_due = $tanggal;
        $pengumuman->due_date = $tanggal;

        $pengumuman->save();

        // 🔹 Jika private, simpan penerima
        if ($pengumuman->is_private && $request->has('recipients')) {
            $recipientIds = array_filter($request->recipients);
            $pengumuman->recipients()->attach($recipientIds);
        }

        DB::commit();
        return redirect()->back()->with('success', 'Pengumuman berhasil dibuat!');
    } catch (\Throwable $th) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $th->getMessage());
    }
}

    /**
     * 🔹 Menghapus pengumuman
     */
    public function destroy(Pengumuman $pengumuman)
    {
        $pengumuman->recipients()->detach();
        $pengumuman->delete();
        return redirect()->back()->with('success', 'Pengumuman berhasil dihapus.');
    }

    /**
     * 🔹 Mendapatkan daftar user untuk pemilihan penerima
     */
    public function getAnggota()
    {
        $members = \App\Models\User::select(
            'id',
            'full_name as name',
            'email',
            DB::raw('NULL as avatar')
        )->get();

        return response()->json($members);
    }

    /**
     * 🔹 Menampilkan isi pengumuman tertentu
     */
    //

    public function show($id)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $pengumuman = Pengumuman::with(['recipients', 'creator'])->findOrFail($id);

        // Safety check: jika tidak boleh lihat, kembalikan 403
        if (!$pengumuman->isVisibleTo($user)) {
            abort(403, 'Anda tidak memiliki akses untuk melihat pengumuman ini.');
        }

        return view('isipengumuman', compact('pengumuman', 'user'));
    }
}

