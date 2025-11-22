<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Pengumuman;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PengumumanPerusahaanController extends Controller
{
    /**
     * Menampilkan semua pengumuman dalam satu perusahaan
     */
    public function index($company_id)
    {
        $user = Auth::user();
        $company = Company::findOrFail($company_id);

        // Ambil semua pengumuman dari semua workspace dalam perusahaan ini
        $pengumumans = Pengumuman::whereHas('workspace', function($query) use ($company_id) {
                $query->where('company_id', $company_id);
            })
            ->withCount('comments')
            ->with('creator', 'workspace')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pengumuman-perusahaan', compact('pengumumans', 'company', 'user'));
    }

    /**
     * Menyimpan pengumuman baru untuk perusahaan
     */
    public function store(Request $request, $company_id)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'workspace_id' => 'required|exists:workspaces,id',
            'due_date' => 'nullable|date',
            'auto_due' => 'nullable|string',
        ]);

        $user = Auth::user();
        $company = Company::findOrFail($company_id);

        // Pastikan workspace yang dipilih belong to perusahaan ini
        $workspace = Workspace::where('id', $request->workspace_id)
            ->where('company_id', $company_id)
            ->firstOrFail();

        DB::beginTransaction();

        try {
            $pengumuman = new Pengumuman();
            $pengumuman->id = Str::uuid();
            $pengumuman->workspace_id = $workspace->id;
            $pengumuman->title = $request->title;
            $pengumuman->description = $request->description;
            $pengumuman->created_by = $user->id;
            $pengumuman->is_private = false; // Selalu public untuk pengumuman perusahaan

            // Logika auto due
            switch (strtolower($request->auto_due ?? '1 hari dari sekarang')) {
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

            DB::commit();

            return redirect()->route('pengumuman-perusahaan.show', $pengumuman->id)->with('alert', [
                'icon' => 'success',
                'title' => 'Berhasil!',
                'text' => 'Berhasil membuat pengumuman perusahaan.'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $th->getMessage());
        }
    }

    /**
     * Menampilkan detail pengumuman perusahaan
     */
    public function show($id)
    {
        $pengumuman = Pengumuman::with(['creator', 'workspace'])->findOrFail($id);
        $user = Auth::user();
        $company = $pengumuman->workspace->company;

        // Hitung komentar
        $commentCount = $pengumuman->comments()->whereNull('parent_comment_id')->count();
        $allCommentCount = $pengumuman->comments()->count();

        return view('isi-pengumuman-perusahaan', compact('pengumuman', 'company', 'commentCount', 'allCommentCount'));
    }

    /**
     * Mendapatkan data workspaces dalam perusahaan untuk dropdown
     * 🔹 PERBAIKAN: Ambil company_id dari request route parameter
     */
    public function getWorkspaces(Request $request, $company_id)
    {
        $workspaces = Workspace::where('company_id', $company_id)
            ->select('id', 'name')
            ->get();

        return response()->json($workspaces);
    }

    /**
     * Mendapatkan data pengumuman untuk edit
     */
    public function getEditData($id)
    {
        $pengumuman = Pengumuman::with('workspace')->findOrFail($id);
        $user = Auth::user();

        // Pastikan hanya pembuat yang bisa mengedit
        if ($pengumuman->created_by !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Tentukan auto_due text berdasarkan due_date
        $autoDueText = '';
        if ($pengumuman->auto_due) {
            $dueDate = Carbon::parse($pengumuman->due_date);
            $now = Carbon::now();
            $diffDays = $now->diffInDays($dueDate, false);

            if ($diffDays === 1)
                $autoDueText = '1 hari dari sekarang';
            else if ($diffDays === 3)
                $autoDueText = '3 hari dari sekarang';
            else if ($diffDays === 7)
                $autoDueText = '7 hari dari sekarang';
            else
                $autoDueText = '';
        }

        // Format data untuk frontend
        $data = [
            'id' => $pengumuman->id,
            'title' => $pengumuman->title,
            'description' => $pengumuman->description,
            'workspace_id' => $pengumuman->workspace_id,
            'due_date' => $pengumuman->due_date ? Carbon::parse($pengumuman->due_date)->toDateString() : null,
            'auto_due' => $autoDueText,
        ];

        return response()->json($data);
    }

    /**
     * Update pengumuman perusahaan
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'workspace_id' => 'required|exists:workspaces,id',
            'due_date' => 'nullable|date',
            'auto_due' => 'nullable|string',
        ]);

        $user = Auth::user();
        $pengumuman = Pengumuman::findOrFail($id);

        if ($pengumuman->created_by !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        DB::beginTransaction();

        try {
            $pengumuman->title = $request->title;
            $pengumuman->description = $request->description;
            $pengumuman->workspace_id = $request->workspace_id;

            // Logika auto due
            $autoDueValue = $request->auto_due ?? '';
            $days = null;

            switch (strtolower($autoDueValue)) {
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
                $pengumuman->due_date = $request->due_date ? Carbon::parse($request->due_date)->toDateString() : null;
            }

            $pengumuman->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengumuman perusahaan berhasil diperbarui.',
                'redirect' => route('pengumuman-perusahaan.show', $pengumuman->id)
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Hapus pengumuman perusahaan
     */
    public function destroy($id)
    {
        $pengumuman = Pengumuman::findOrFail($id);
        $user = Auth::user();
        $company_id = $pengumuman->workspace->company_id;

        if ($pengumuman->created_by !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        DB::beginTransaction();

        try {
            // Hapus relasi recipients jika ada
            $pengumuman->recipients()->detach();

            // Hapus pengumuman
            $pengumuman->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengumuman perusahaan berhasil dihapus.',
                'redirect_url' => route('pengumuman-perusahaan.index', $company_id)
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage()
            ], 500);
        }
    }
    
}
