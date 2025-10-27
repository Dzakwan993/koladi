<?php

// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use App\Models\Pengumuman;
// use Illuminate\Support\Str;
// use Illuminate\Support\Facades\Auth;
// use Carbon\Carbon;
// use Illuminate\Support\Facades\DB;

// class PengumumanController extends Controller
// {
//     /**
//      * 🔹 Menampilkan daftar pengumuman yang bisa dilihat user
//      */

//     public function index()
// {
//     $user = Auth::user();

//     // Ambil workspace_id user
//     $workspaceId = DB::table('user_workspaces')
//     ->where('user_id', $user->id)
//     ->value('workspace_id');


//     // Ambil semua pengumuman dalam workspace user
//     $pengumumans = \App\Models\Pengumuman::where('workspace_id', $workspaceId)
//         ->with('recipients') // kita tambahkan relasi nanti
//         ->orderBy('created_at', 'desc')
//         ->get();

//     return view('pengumuman', compact('pengumumans', 'user'));
// }


//     /**
//      * 🔹 Menyimpan pengumuman baru
//      */
//     public function store(Request $request)
//     {
//         $request->validate([
//             'title' => 'required|string',
//             'description' => 'required|string',
//             'due_date' => 'nullable|date',
//             'is_private' => 'nullable|boolean',
//             'auto_due' => 'nullable|string',
//             'recipients' => 'nullable|array', // daftar user_id penerima private
//         ]);

//         $user = Auth::user();
//         if (!$user) {
//             return redirect()->back()->with('error', 'User belum login!');
//         }

//         DB::beginTransaction();

//         try {
//             $pengumuman = new Pengumuman();
//             $pengumuman->id = Str::uuid();
//             $pengumuman->title = $request->title;
//             $pengumuman->description = $request->description;
//             $pengumuman->is_private = $request->has('is_private');
//             $pengumuman->workspace_id = $user->workspace_id;
//             $pengumuman->created_by = $user->id;

//             // Logika due date otomatis
//             switch (strtolower($request->auto_due)) {
//                 case '1 hari dari sekarang':
//                     $days = 1;
//                     break;
//                 case '3 hari dari sekarang':
//                     $days = 3;
//                     break;
//                 case '7 hari dari sekarang':
//                     $days = 7;
//                     break;
//                 default:
//                     $days = null;
//                     break;
//             }

//             if ($days !== null) {
//                 $tanggal = Carbon::now()->addDays($days)->toDateString();
//                 $pengumuman->auto_due = $tanggal;
//                 $pengumuman->due_date = $tanggal;
//             } else {
//                 $pengumuman->auto_due = null;
//                 $pengumuman->due_date = $request->due_date ?? null;
//             }

//             $pengumuman->save();

//             // Jika pengumuman private, simpan penerimanya ke tabel pivot
//             if ($pengumuman->is_private && $request->has('recipients')) {
//                 $recipientIds = array_filter($request->recipients);
//                 $pengumuman->recipients()->attach($recipientIds);
//             }

//             DB::commit();

//             return redirect()->back()->with('success', 'Pengumuman berhasil dibuat!');
//         } catch (\Throwable $th) {
//             DB::rollBack();
//             return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $th->getMessage());
//         }
//     }

//     /**
//      * 🔹 Menghapus pengumuman
//      */
//     public function destroy(Pengumuman $pengumuman)
//     {
//         $pengumuman->recipients()->detach(); // hapus relasi penerima dulu
//         $pengumuman->delete();
//         return redirect()->back()->with('success', 'Pengumuman berhasil dihapus.');
//     }


//     /**
//      * 🔹 Mendapatkan daftar anggota workspace untuk pemilihan penerima pengumuman
//      */
//    public function getAnggota()
// {
//     // Ambil semua user dari tabel users
//     $members = \App\Models\User::select(
//         'id',
//         'full_name as name',
//         'email',
//         DB::raw('NULL as avatar') // Jika nanti mau pakai avatar, bisa diganti field sebenarnya
//     )->get();

//     return response()->json($members);
// }



// public function show($id)
// {
//     $pengumuman = \App\Models\Pengumuman::with('recipients', 'creator')->findOrFail($id);
//     $user = Auth::user();

//     // cek akses
//     $bolehLihat = !$pengumuman->is_private || $pengumuman->recipients->contains('user_id', $user->id);

//     if (!$bolehLihat) {
//         abort(403, 'Anda tidak memiliki akses untuk melihat pengumuman ini.');
//     }

//     return view('isipengumuman', compact('pengumuman'));
// }


// }

// ini tanpa id_workspace

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

        // Ambil semua pengumuman yang dibuat oleh user
        $pengumumans = Pengumuman::where('created_by', $user->id)
            ->with('recipients')
            ->orderBy('created_at', 'desc')
            ->get();

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

            // Logika auto due
            switch (strtolower($request->auto_due)) {
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
                    $days = null;
                    break;
            }

            if ($days !== null) {
                $tanggal = Carbon::now()->addDays($days)->toDateString();
                $pengumuman->auto_due = $tanggal;
                $pengumuman->due_date = $tanggal;
            } else {
                $pengumuman->auto_due = null;
                $pengumuman->due_date = $request->due_date ?? null;
            }

            $pengumuman->save();

            // Jika private, simpan penerima
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
    public function show($id)
    {
        $pengumuman = Pengumuman::with('recipients', 'creator')->findOrFail($id);
        $user = Auth::user();

        // Cek akses
        $bolehLihat = !$pengumuman->is_private || $pengumuman->recipients->contains('user_id', $user->id);

        if (!$bolehLihat) {
            abort(403, 'Anda tidak memiliki akses untuk melihat pengumuman ini.');
        }

        return view('isipengumuman', compact('pengumuman'));
    }
}

