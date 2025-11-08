<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengumuman;
use App\Models\Workspace;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PengumumanController extends Controller
{
    /**
     * 🔹 Menampilkan daftar pengumuman berdasarkan user ID saja
     */
    public function index($id)
    {
        $user = Auth::user();
        $workspace = Workspace::findOrFail($id);

        $pengumumans = Pengumuman::where('workspace_id', $workspace->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pengumuman', compact('pengumumans', 'workspace', 'user'));
    }

    public function show($id)
    {
        $pengumuman = Pengumuman::with(['recipients', 'creator'])->findOrFail($id);
        $user = Auth::user();

        // Kalau private, cek apakah dia pembuat atau penerima
        if ($pengumuman->is_private) {
            $recipientIds = $pengumuman->recipients->pluck('user_id')->toArray();

            // Jika bukan pembuat dan bukan penerima → tetap private, tapi bisa lihat
            if ($pengumuman->creator->id !== $user->id && !in_array($user->id, $recipientIds)) {
                // Masih private, tapi kita izinkan lihat
                // Kalau mau bisa kasih flag untuk blade: $bolehLihat = false misal
            }
        }

        return view('isipengumuman', compact('pengumuman'));
    }



    /**
     * 🔹 Menyimpan pengumuman baru
     */
    public function store(Request $request, $id_workspace)
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
            $pengumuman->workspace_id = $id_workspace;
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
            return back()->with('alert', [
                'icon' => 'success', // success, error, warning, info
                'title' => 'Berahasil!',
                'text' => 'Berhasil membuat pengumuman.'
            ]);

            // return redirect()->back()->with('success', 'Pengumuman berhasil dibuat!');
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

    public function getAnggota($workspaceId)
    {
        $members = DB::table('users')
            ->join('user_workspaces', 'users.id', '=', 'user_workspaces.user_id')
            ->where('user_workspaces.workspace_id', $workspaceId)
            ->select(
                'users.id',
                'users.full_name as name',
                'users.email',
                DB::raw('NULL as avatar')
            )
            ->get();

        return response()->json($members);
    }
}
