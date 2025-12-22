<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Company;
use App\Models\Pengumuman;
use App\Models\Role;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationService;

class PengumumanPerusahaanController extends Controller
{


    // ✅ TAMBAH INI
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Helper untuk set timezone user
     */
    private function setUserTimezone()
    {
        if (Auth::check()) {
            $user = Auth::user();
            $userTimezone = $user->timezone ??
                request()->cookie('user_timezone') ??
                config('app.timezone');

            // Set timezone
            config(['app.timezone' => $userTimezone]);
            date_default_timezone_set($userTimezone);

            return $userTimezone;
        }

        return config('app.timezone');
    }

    /**
     * Ambil role user di company tertentu (role ada di pivot company_user.role)
     */
    private function getRoleInCompany($user, $company_id): string
    {
        // Ambil roles_id dari pivot user_companies
        $rolesId = $user->companies()
            ->where('companies.id', $company_id)
            ->first()
            ?->pivot
            ?->roles_id;

        // Kalau gak ketemu, paling aman anggap member
        if (!$rolesId) return 'member';

        // Ambil nama role dari tabel roles
        $roleName = Role::where('id', $rolesId)->value('name');

        return strtolower($roleName ?? 'member');
    }



    /**
     * Cegah member melakukan aksi kelola pengumuman
     */
    private function ensureNotMember($user, $company_id): void
    {
        $roleName = $this->getRoleInCompany($user, $company_id);

        if ($roleName === 'member') {
            abort(403, 'Member tidak diizinkan membuat / mengubah / menghapus pengumuman');
        }
    }



    /**
     * Menampilkan semua pengumuman dalam satu perusahaan
     */
    public function index(Request $request, $company_id)
    {
        // SET TIMEZONE USER
        $userTimezone = $this->setUserTimezone();

        $user = Auth::user();

        // validasi apakah user member perusahaan ini
        if (!$user->companies()->where('company_id', $company_id)->exists()) {
            abort(403, 'Unauthorized access');
        }

        $pengumumans = Pengumuman::where('company_id', $company_id)
            ->withCount('comments')
            ->whereNull('workspace_id')
            ->where(function ($q) use ($user) {
                $q->where('is_private', false)
                    ->orWhere('created_by', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // FORMAT WAKTU CREATED_AT SAJA UNTUK USER
        foreach ($pengumumans as $pengumuman) {
            $pengumuman->display_created_at = Carbon::parse($pengumuman->created_at)
                ->setTimezone($userTimezone)
                ->translatedFormat('d M Y H:i');

            $pengumuman->display_relative_time = Carbon::parse($pengumuman->created_at)
                ->setTimezone($userTimezone)
                ->diffForHumans();
        }

        return view('pengumuman-perusahaan', compact('pengumumans', 'company_id'));
    }

    /**
     * Menyimpan pengumuman baru untuk perusahaan
     */
    public function store(Request $request, $company_id)
    {
        // SET TIMEZONE USER
        $this->setUserTimezone();

        $user = Auth::user();

        // Validasi user memiliki akses ke company ini
        if (!$user->companies->contains($company_id)) {
            abort(403, 'Unauthorized access to this company');
        }

        $this->ensureNotMember($user, $company_id);

        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'due_date' => 'nullable|date',
            'auto_due' => 'nullable|string',
            'is_private' => 'nullable|boolean',
        ]);

        $company = Company::findOrFail($company_id);

        DB::beginTransaction();

        try {
            $pengumuman = new Pengumuman();
            $pengumuman->id = Str::uuid();
            $pengumuman->company_id = $company_id;
            $pengumuman->workspace_id = null; // Karena workspace dihapus dari form
            $pengumuman->title = $request->title;
            $pengumuman->description = $request->description;
            $pengumuman->created_by = $user->id;
            $pengumuman->is_private = $request->is_private ?? false;

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

                        $this->notificationService->notifyAnnouncementCreated($pengumuman);


            return redirect()->route('pengumuman-perusahaan.show', ['company_id' => $company_id, 'id' => $pengumuman->id])->with('alert', [
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
    public function show($company_id, $id)
    {
        // SET TIMEZONE USER
        $userTimezone = $this->setUserTimezone();

        $user = Auth::user();

        // Validasi user memiliki akses ke company ini
        if (!$user->companies->contains($company_id)) {
            abort(403, 'Unauthorized access to this company');
        }

        $pengumuman = Pengumuman::with(['creator', 'workspace'])->findOrFail($id);

        // Validasi pengumuman belong to company
        if ($pengumuman->company_id != $company_id) {
            abort(404);
        }

        // Validasi akses user ke pengumuman
        if (!$pengumuman->isVisibleTo($user)) {
            abort(403, 'Anda tidak memiliki akses ke pengumuman ini');
        }

        // FORMAT WAKTU CREATED_AT SAJA UNTUK USER
        $pengumuman->display_created_at = Carbon::parse($pengumuman->created_at)
            ->setTimezone($userTimezone)
            ->translatedFormat('d M Y H:i');

        $pengumuman->display_relative_time = Carbon::parse($pengumuman->created_at)
            ->setTimezone($userTimezone)
            ->diffForHumans();

        $company = Company::findOrFail($company_id);

        $commentCount = $pengumuman->comments()->whereNull('parent_comment_id')->count();
        $allCommentCount = $pengumuman->comments()->count();

        return view('pengumuman-perusahaan-detail', compact(
            'pengumuman',
            'company',
            'company_id',
            'commentCount',
            'allCommentCount',
            'user'
        ));
    }

    /**
     * Mendapatkan data workspaces dalam perusahaan untuk dropdown
     */
    public function getWorkspaces(Request $request, $company_id)
    {
        $user = Auth::user();

        // Validasi user memiliki akses ke company ini
        if (!$user->companies->contains($company_id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $workspaces = Workspace::where('company_id', $company_id)
            ->select('id', 'name')
            ->get();

        return response()->json($workspaces);
    }

    /**
     * Mendapatkan data pengumuman untuk edit
     */
    public function getEditData($company_id, $id)
    {
        // SET TIMEZONE USER
        $this->setUserTimezone();

        $user = Auth::user();

        // Validasi user memiliki akses ke company ini
        if (!$user->companies->contains($company_id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $this->ensureNotMember($user, $company_id);

        $pengumuman = Pengumuman::findOrFail($id);

        // Validasi pengumuman belong to company
        if ($pengumuman->company_id != $company_id) {
            return response()->json(['error' => 'Pengumuman tidak ditemukan'], 404);
        }

        // Validasi akses user ke pengumuman
        if (!$pengumuman->isVisibleTo($user)) {
            return response()->json(['error' => 'Unauthorized access to this announcement'], 403);
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
                $autoDueText = '';
        }

        // Format data untuk frontend
        $data = [
            'id' => $pengumuman->id,
            'title' => $pengumuman->title,
            'description' => $pengumuman->description,
            'due_date' => $pengumuman->due_date ? Carbon::parse($pengumuman->due_date)->toDateString() : null,
            'auto_due' => $autoDueText,
            'is_private' => $pengumuman->is_private,
        ];

        return response()->json($data);
    }

    /**
     * Update pengumuman perusahaan
     */
    public function update(Request $request, $company_id, $id)
    {
        // SET TIMEZONE USER
        $this->setUserTimezone();

        $user = Auth::user();

        // Validasi user memiliki akses ke company ini
        if (!$user->companies->contains($company_id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $this->ensureNotMember($user, $company_id);

        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'due_date' => 'nullable|date',
            'auto_due' => 'nullable|string',
            'is_private' => 'nullable|boolean',
        ]);

        $pengumuman = Pengumuman::findOrFail($id);

        // Validasi pengumuman belong to company
        if ($pengumuman->company_id != $company_id) {
            return response()->json(['error' => 'Pengumuman tidak ditemukan'], 404);
        }

        // Validasi akses user ke pengumuman
        if (!$pengumuman->isVisibleTo($user)) {
            return response()->json(['error' => 'Unauthorized access to this announcement'], 403);
        }

        if ($pengumuman->created_by !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        DB::beginTransaction();

        try {
            $pengumuman->title = $request->title;
            $pengumuman->description = $request->description;
            $pengumuman->is_private = $request->is_private ?? false;

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
                'redirect' => route('pengumuman-perusahaan.show', ['company_id' => $company_id, 'id' => $pengumuman->id])
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
    public function destroy($company_id, $id)
    {
        $user = Auth::user();

        if (!$user->companies->contains($company_id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $this->ensureNotMember($user, $company_id);

        $pengumuman = Pengumuman::findOrFail($id);

        if ($pengumuman->company_id != $company_id) {
            return response()->json(['error' => 'Pengumuman tidak ditemukan'], 404);
        }

        if (!$pengumuman->isVisibleTo($user)) {
            return response()->json(['error' => 'Unauthorized access to this announcement'], 403);
        }

        if ($pengumuman->created_by !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

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
