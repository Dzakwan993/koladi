<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\DocumentRecipient;
use App\Models\Folder;
use App\Models\File;
use Illuminate\Support\Str; // ⬅⬅ Tambahkan ini
use Illuminate\Http\Request;
use App\Http\Requests\StoreFolderRequest;
use App\Http\Requests\StoreFileRequest;
use App\Models\UserWorkspace;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DokumenController extends Controller
{
    public function index(Workspace $workspace)
    {
        // Cek apakah user punya akses ke workspace ini
        // $this->authorize('view', $workspace);

        $userId = auth()->id();

        // Ambil folders
        // Jika user adalah creator/admin workspace, tampilkan semua
        // Jika bukan, hanya tampilkan folder public dan folder private yang dia buat
        $folders = Folder::where('workspace_id', $workspace->id)
            ->where(function ($query) use ($userId) {
                $query->where('is_private', false)
                    ->orWhere(function ($q) use ($userId) {
                        $q->where('is_private', true)
                          ->where('created_by', $userId);
                    });
            })
            ->with(['creator', 'files' => function ($query) use ($userId) {
                // Filter files juga berdasarkan is_private
                $query->where(function ($q) use ($userId) {
                    $q->where('is_private', false)
                      ->orWhere('uploaded_by', $userId);
                });
            }])
            ->withCount(['files' => function ($query) use ($userId) {
                $query->where(function ($q) use ($userId) {
                    $q->where('is_private', false)
                      ->orWhere('uploaded_by', $userId);
                });
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        // Ambil files yang tidak ada dalam folder (root files)
        $rootFiles = File::where('workspace_id', $workspace->id)
            ->where(function ($query) use ($userId) {
                $query->where('is_private', false)
                    ->orWhere('uploaded_by', $userId);
            })
            ->with('uploader')
            ->orderBy('uploaded_at', 'desc')
            ->get();

        return view('dokumen-dan-file', compact('workspace', 'folders', 'rootFiles'));
    }


    public function store(StoreFolderRequest $request)
    {
        $validated = $request->validated();

        Folder::create([
            'workspace_id' => $validated['workspace_id'],
            'name' => $validated['name'],
            'is_private' => $validated['is_private'] ?? false,
            'created_by' => auth()->id(),
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        return redirect()->back()->with('alert', [
            'icon' => 'success',
            'title' => 'Folder berhasil dibuat!',
            'text' => 'Data folder baru sudah tersimpan.',
        ])
          ->with('alert_once', true);;
    }

    public function storeFile(StoreFileRequest $request)
        {
            $validated = $request->validated();
            $uploadedBy = auth()->id();

            $uploaded = $request->file('file');

            // --------- [1] Ambil nama awal ---------
            $originalName = pathinfo($uploaded->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $uploaded->getClientOriginalExtension();

            $workspaceId = $validated['workspace_id'];
            $folderId = $validated['folder_id'] ?? null;

            // --------- [2] Generate nama unik ---------
            $newName = $originalName;
            $counter = 1;

            while (
                File::where('workspace_id', $workspaceId)
                    ->where('folder_id', $folderId)
                    ->where('file_name', $newName . '.' . $extension)
                    ->exists()
            ) {
                $newName = $originalName . '(' . $counter . ')';
                $counter++;
            }

            $finalName = $newName . '.' . $extension;

            // --------- [3] Simpan fisik dengan nama final ---------
            $path = $uploaded->storeAs(
                'files',
                $finalName,
                'public' // ⬅️ Tambahkan disk public
            );

            // --------- [4] Simpan ke database ---------
            $fileModel = File::create([
                'workspace_id' => $workspaceId,
                'folder_id' => $folderId,
                'uploaded_by' => $uploadedBy,
                'file_name' => $finalName,
                'file_path' => $path,
                'file_size' => $uploaded->getSize(),
                'file_type' => $extension,
                'file_url' => asset('storage/' . $path),
                'is_private' => false,
                'uploaded_at' => now(),
            ]);

            return redirect()->back()->with('alert', [
                'icon' => 'success',
                'title' => 'File berhasil diunggah!',
                'text' => 'File ' . $fileModel->file_name . ' sudah tersimpan.',
            ])->with('alert_once', true);
        }


        public function updateFolder(Request $request, $id)
        {
            $folder = Folder::findOrFail($id);

            $oldName = $folder->name;
            $oldPrivate = $folder->is_private;

            $newName = $request->name;
            $newPrivate = $request->is_private == 1;

            $messages = [];

            // Perubahan nama
            if ($oldName !== $newName) {
                $messages[] = "Nama folder diperbarui.";
            }

            // Perubahan private/public, tapi kalimat dibuat netral
            if ($oldPrivate !== $newPrivate) {
                $messages[] = "Aturan privasi folder diperbarui.";
            }

            // Jika tidak ada perubahan sama sekali
            if (empty($messages)) {
                $messages[] = "Tidak ada perubahan pada folder.";
            }

            // Update data
            $folder->update([
                'name' => $newName,
                'is_private' => $newPrivate,
            ]);

            return redirect()->back()->with('alert', [
                'icon'  => 'success',
                'title' => 'Folder berhasil diperbarui!',
                'text'  => implode(" ", $messages),
            ])->with('alert_once', true);
        }

        public function updateFile(Request $request, $id)
        {
            $file = File::findOrFail($id);

            $oldName = $file->name;
            $oldPrivate = $file->is_private;

            $newName = $request->name;
            $newPrivate = $request->is_private == 1;

            $messages = [];

            // Nama berubah
            if ($oldName !== $newName) {
                $messages[] = "Nama file diperbarui.";
            }

            // Privasi berubah
            if ($oldPrivate !== $newPrivate) {
                $messages[] = "Aturan privasi file diperbarui.";
            }

            // Tidak ada perubahan
            if (empty($messages)) {
                $messages[] = "Tidak ada perubahan pada file.";
            }

            // Update data
            $file->update([
                'name' => $newName,
                'is_private' => $newPrivate,
            ]);

            return redirect()->back()->with('alert', [
                'icon'  => 'success',
                'title' => 'File berhasil diperbarui!',
                'text'  => implode(" ", $messages),
            ])->with('alert_once', true);
        }

        public function destroy($id)
        {
            $file = File::findOrFail($id);

            // Hapus file dari storage
            if ($file->path && Storage::exists($file->path)) {
                Storage::delete($file->path);
            }

            $file->delete();

            return redirect()->back()->with('alert', [
                'icon' => 'success',
                'title' => 'File berhasil dihapus!',
                'text' => 'Data file sudah dihapus dari sistem.',
            ])->with('alert_once', true);
        }
        
        public function destroyFolder(Folder $folder)
        {
            // Hapus semua file dalam folder ini
            foreach ($folder->files as $file) {
                $file->delete();
            }

            // Hapus semua subfolder (rekursif)
            $this->deleteSubfolders($folder);

            // Hapus folder utama
            $folder->delete();

            return redirect()->back()->with('alert', [
                'icon'  => 'success',
                'title' => 'Folder berhasil dihapus!',
                'text'  => 'Semua data di dalam folder sudah terhapus.',
            ])->with('alert_once', true);
        }

        private function deleteSubfolders($folder)
        {
            foreach ($folder->children as $sub) {

                // Hapus file di subfolder ini
                foreach ($sub->files as $file) {
                    $file->delete();
                }

                // Rekursif untuk subfolder berikutnya
                $this->deleteSubfolders($sub);

                // Hapus foldernya
                $sub->delete();
            }
        }

        public function getWorkspaceMembers(Request $req, $workspaceId)
        {
            $user = Auth::user();
            $activeCompanyId = session('active_company_id');

            // ================================
            // 1. CEK AKSES ROLE COMPANY
            // ================================

            $userCompany = $user->userCompanies()
                ->where('company_id', $activeCompanyId)
                ->with('role')
                ->first();

            $companyRole = $userCompany?->role?->name ?? 'Member';
            $companyHighRoles = ['SuperAdmin', 'Administrator', 'Admin', 'Manager'];

            $isHighRole = in_array($companyRole, $companyHighRoles);

            // ================================
            // 2. BACA KONTEKS (folder/file)
            // ================================

            $folderCreatedBy = $req->folder_created_by ?: null;
            $fileUploadedBy  = $req->file_uploaded_by ?: null;

            // Jika BUKAN role tinggi,
            // tetap izinkan kalau dia adalah pembuat folder / pengupload file
            if (!$isHighRole) {

                $userWorkspace = UserWorkspace::where('user_id', $user->id)
                    ->where('workspace_id', $workspaceId)
                    ->where('status_active', true)
                    ->with('role')
                    ->first();

                $workspaceRole = $userWorkspace?->role?->name ?? 'Member';

                // kalau workspace role juga bukan manager
                if ($workspaceRole !== 'Manager') {

                    // tetapi jika dia pembuat folder atau uploader → IZINKAN
                    if ($folderCreatedBy != $user->id && $fileUploadedBy != $user->id) {
                        return response()->json([
                            'error' => 'Anda tidak memiliki akses untuk melihat anggota workspace ini'
                        ], 403);
                    }
                }
            }

            // ================================
            // 3. AMBIL SEMUA MEMBER
            // ================================

            $members = User::whereIn('id',
                UserWorkspace::where('workspace_id', $workspaceId)->pluck('user_id')
            )->get();

            // ================================
            // 4. IZIN PILIH MEMBER
            // ================================

            $filtered = $members->map(function ($m) use ($folderCreatedBy, $fileUploadedBy, $isHighRole) {

                $canBeSelected = false;

                // Role tinggi → bisa pilih siapa pun
                if ($isHighRole) {
                    $canBeSelected = true;
                }

                // pembuat folder → hanya dirinya yang bisa dipilih
                if ($folderCreatedBy && $m->id == $folderCreatedBy) {
                    $canBeSelected = true;
                }

                // pengupload file → hanya dirinya yang bisa dipilih
                if ($fileUploadedBy && $m->id == $fileUploadedBy) {
                    $canBeSelected = true;
                }

                return [
                    'id'             => $m->id,
                    'name'           => $m->full_name ?? $m->name,
                    'avatar'         => $m->avatar ?? 'https://i.pravatar.cc/150?img=' . rand(1, 70),
                    'can_be_selected'=> $canBeSelected,
                    'selected'       => false,
                ];
            });

            return response()->json([
                'members' => $filtered
            ]);
        }

     public function recipientsStore(Request $request)
        {
            $request->validate([
                'document_id' => 'required|uuid',
                'selected_members' => 'required|json',
            ]);

            $selectedMembers = json_decode($request->selected_members, true) ?? [];

            // Tandai semua yang ada di database tapi tidak dipilih => status false
            DocumentRecipient::where('document_id', $request->document_id)
                ->whereNotIn('user_id', $selectedMembers)
                ->update(['status' => false]);

            // Tambahkan atau update recipients
            foreach ($selectedMembers as $userId) {
                DocumentRecipient::updateOrCreate(
                    [
                        'document_id' => $request->document_id,
                        'user_id' => $userId
                    ],
                    [
                        'id' => Str::uuid(),
                        'status' => true
                    ]
                );
            }

            // Cek apakah ada minimal 1 recipient aktif
            $hasActiveRecipients = DocumentRecipient::where('document_id', $request->document_id)
                ->where('status', true)
                ->exists();

            // Update kolom is_private di files atau folders
            if ($file = File::find($request->document_id)) {
                $file->update(['is_private' => $hasActiveRecipients]);
            } elseif ($folder = Folder::find($request->document_id)) {
                $folder->update(['is_private' => $hasActiveRecipients]);
            }

            return redirect()->back()->with('alert', [
                'icon' => 'success',
                'title' => 'Berhasil!',
                'text' => 'Penerima berkas berhasil diperbarui.',
            ])->with('alert_once', true);
        }



        public function getRecipients($documentId)
        {
            // Ambil hanya user_id yang status = true
            $recipients = DocumentRecipient::where('document_id', $documentId)
                ->where('status', true)
                ->pluck('user_id'); // ambil array user_id

            return response()->json(['recipients' => $recipients]);
        }








}