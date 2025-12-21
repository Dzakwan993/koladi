<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;
use App\Models\Pengumuman;
use App\Models\Workspace;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
            ->withCount('comments')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pengumuman', compact('pengumumans', 'workspace', 'user'));
    }

    /**
     * 🔹 Menampilkan detail pengumuman
     */
    public function show(Workspace $workspace, Pengumuman $pengumuman) // Tambahkan Workspace parameter
    {
        $user = Auth::user();

        // Validasi: pastikan pengumuman milik workspace ini
        if ($pengumuman->workspace_id !== $workspace->id) {
            abort(404);
        }

        // Kalau private, cek apakah dia pembuat atau penerima
        if ($pengumuman->is_private) {
            $recipientIds = $pengumuman->recipients->pluck('user_id')->toArray();

            // Jika bukan pembuat dan bukan penerima → tetap private, tapi bisa lihat
            if ($pengumuman->creator->id !== $user->id && !in_array($user->id, $recipientIds)) {
                // Masih private, tapi kita izinkan lihat
                // Kalau mau bisa kasih flag untuk blade: $bolehLihat = false misal
            }
        }

        // hitung komentar utama saja
        $commentCount = $pengumuman->comments()->whereNull('parent_comment_id')->count();

        // hitung semua komentar termasuk balasan
        $allCommentCount = $pengumuman->comments()->count();

        return view('isiPengumuman', compact('pengumuman', 'workspace', 'commentCount', 'allCommentCount'));
    }


    /**
     * 🔹 Menyimpan pengumuman baru
     */
    public function store(Request $request, Workspace $workspace) // Ganti parameter menjadi Workspace $workspace
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
            $pengumuman->workspace_id = $workspace->id; // Gunakan $workspace->id
            $pengumuman->title = $request->title;
            $pengumuman->description = $request->description;
            $pengumuman->created_by = $user->id;

            // 🔹 Cek apakah switch Rahasia diaktifkan
            $isPrivate = $request->boolean('is_private');

            // 🔹 Kalau switch OFF → abaikan semua pilihan member dan jadikan public
            if (!$isPrivate) {
                $pengumuman->is_private = false;
            } else {
                // 🔹 Kalau switch ON → pastikan ada penerima yang dipilih
                $pengumuman->is_private = true;
            }

            // 🔹 Logika auto due
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

            // 🔹 Kalau private dan ada member dipilih → simpan ke tabel recipients
            if ($pengumuman->is_private) {
                $recipientIds = array_filter($request->input('recipients', []));
                if (!empty($recipientIds)) {
                    $pengumuman->recipients()->attach($recipientIds);
                }
            } else {
                // Kalau public, jangan simpan apa-apa di recipients
                $pengumuman->recipients()->detach();
            }

            // 🔹 Update lampiran yang baru diupload
            DB::table('attachments')
                ->whereNull('attachable_id')
                ->where('uploaded_by', $user->id)
                ->update([
                    'attachable_id' => $pengumuman->id,
                    'attachable_type' => 'App\\Models\\Pengumuman',
                ]);

            DB::commit();

            // PERBAIKAN: Redirect dengan parameter yang benar
            return redirect()->route('pengumuman.show', [
                'workspace' => $workspace->id, // Gunakan $workspace->id
                'pengumuman' => $pengumuman->id
            ])->with('alert', [
                'icon' => 'success',
                'title' => 'Berhasil!',
                'text' => 'Berhasil membuat pengumuman.'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $th->getMessage());
        }
    }


    /**
     * 🔹 Menampilkan form edit pengumuman
     */
    public function getEditData(Workspace $workspace, Pengumuman $pengumuman) // Tambahkan parameter Workspace
    {
        $user = Auth::user();

        // Validasi: pastikan pengumuman milik workspace ini
        if ($pengumuman->workspace_id !== $workspace->id) {
            abort(404);
        }

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
                $autoDueText = ''; // Jika tidak sesuai dengan opsi yang ada
        }

        // Format data untuk frontend
        $data = [
            'id' => $pengumuman->id,
            'title' => $pengumuman->title,
            'description' => $pengumuman->description,
            'is_private' => (bool) $pengumuman->is_private,
            'due_date' => $pengumuman->due_date ? Carbon::parse($pengumuman->due_date)->toDateString() : null,
            'auto_due' => $autoDueText,
            'recipients' => $pengumuman->recipients->pluck('id')->toArray(),
            'recipients_data' => $pengumuman->recipients->map(function ($recipient) {
                $avatarPath = $recipient->avatar ? 'storage/' . $recipient->avatar : null;
                $hasAvatarFile = $avatarPath && file_exists(public_path($avatarPath));

                $avatarUrl = $hasAvatarFile
                    ? asset($avatarPath)
                    : ($recipient->full_name
                        ? 'https://ui-avatars.com/api/?name=' . urlencode($recipient->full_name) . '&background=random&color=fff'
                        : asset('images/dk.jpg'));

                return [
                    'id' => $recipient->id,
                    'name' => $recipient->full_name,
                    'email' => $recipient->email,
                    'avatar' => $avatarUrl,
                ];
            })->toArray()
        ];

        return response()->json($data);
    }
    /**
     * 🔹 Menyimpan perubahan pengumuman
     */
    public function update(Request $request, Workspace $workspace, Pengumuman $pengumuman) // Tambahkan parameter Workspace
    {
        // Validasi: pastikan pengumuman milik workspace ini
        if ($pengumuman->workspace_id !== $workspace->id) {
            abort(404);
        }

        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'due_date' => 'nullable|date',
            'is_private' => 'nullable|boolean',
            'auto_due' => 'nullable|string',
            'recipients' => 'nullable|array',
        ]);

        $user = Auth::user();

        if ($pengumuman->created_by !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        DB::beginTransaction();

        try {
            $pengumuman->title = $request->title;
            $pengumuman->description = $request->description;
            $pengumuman->is_private = $request->boolean('is_private'); // Use boolean() method

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

            // Update recipients jika private
            if ($pengumuman->is_private) {
                $recipientIds = array_filter($request->recipients ?? []);
                $pengumuman->recipients()->sync($recipientIds);
            } else {
                $pengumuman->recipients()->detach();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengumuman berhasil diperbarui.',
                'redirect' => route('pengumuman.show', [
                    'workspace' => $workspace->id,
                    'pengumuman' => $pengumuman->id
                ])
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
     * 🔹 Menghapus pengumuman
     */

    public function destroy(Workspace $workspace, Pengumuman $pengumuman) // Tambahkan parameter Workspace
    {
        // Validasi: pastikan pengumuman milik workspace ini
        if ($pengumuman->workspace_id !== $workspace->id) {
            abort(404);
        }

        $workspaceId = $pengumuman->workspace_id;

    DB::beginTransaction();

    try {
        $pengumuman->recipients()->detach();

        // 1. Kumpulkan SEMUA URL file yang akan dihapus
        $filesToDelete = [];

        // A. Dari tabel attachments
        $attachments = Attachment::where('attachable_type', 'App\\Models\\Pengumuman')
            ->where('attachable_id', $pengumuman->id)
            ->get();

        foreach ($attachments as $attachment) {
            $filesToDelete[] = $attachment->file_url;
        }

        // B. Dari description (CKEditor content)
        if (!empty($pengumuman->description)) {
            preg_match_all('/(\/storage\/uploads\/(files|images)\/[^\s"\'<>]+)/i', $pengumuman->description, $matches);

            if (!empty($matches[1])) {
                foreach ($matches[1] as $fileUrl) {
                    $fullUrl = Str::startsWith($fileUrl, 'http') ? $fileUrl : url($fileUrl);
                    $filesToDelete[] = $fullUrl;
                }
            }
        }

        // 2. Hapus file HANYA jika tidak digunakan oleh pengumuman lain
        $filesToDelete = array_unique($filesToDelete);

        foreach ($filesToDelete as $fileUrl) {
            $fileName = basename($fileUrl);

            // CEK 1: Apakah file ini ada di attachments pengumuman lain?
            $usedInAttachments = Attachment::where('attachable_type', 'App\\Models\\Pengumuman')
                ->where('attachable_id', '!=', $pengumuman->id)
                ->where('file_url', 'like', '%' . $fileName)
                ->exists();

            // CEK 2: Apakah file ini ada di description pengumuman lain?
            $usedInDescription = Pengumuman::where('id', '!=', $pengumuman->id)
                ->where('description', 'like', '%' . $fileName . '%')
                ->exists();

            // Jika file masih digunakan di pengumuman lain (baik di attachments ATAU description)
            if ($usedInAttachments || $usedInDescription) {
                // SKIP - jangan hapus file
                continue;
            }

            // File tidak digunakan lagi, aman untuk dihapus
            $relativePath = null;
            if (Str::contains($fileUrl, '/storage/uploads/files/')) {
                $relativePath = 'uploads/files/' . $fileName;
            } elseif (Str::contains($fileUrl, '/storage/uploads/images/')) {
                $relativePath = 'uploads/images/' . $fileName;
            }

            if ($relativePath && Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
            }
        }

        // 3. Hapus record attachments dari database
        Attachment::where('attachable_type', 'App\\Models\\Pengumuman')
            ->where('attachable_id', $pengumuman->id)
            ->delete();

        // 4. Hapus comments
        $pengumuman->comments()->delete();

        // 5. Soft delete pengumuman
        $pengumuman->delete();

        DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengumuman berhasil dihapus.',
                'redirect_url' => route('workspace.pengumuman', $workspaceId)
            ]);
    } catch (\Throwable $th) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan: ' . $th->getMessage()
        ], 500);
    }
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
                'users.avatar'
            )
            ->get()
            ->map(function ($user) {
                $avatarPath = $user->avatar ? 'storage/' . $user->avatar : null;
                $hasAvatarFile = $avatarPath && file_exists(public_path($avatarPath));

                $avatarUrl = $hasAvatarFile
                    ? asset($avatarPath)
                    : ($user->name
                        ? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=random&color=fff'
                        : asset('images/dk.jpg'));

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $avatarUrl,
                ];
            });

        return response()->json($members);
    }

}
