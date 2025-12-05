<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Folder;
use App\Models\File;
use App\Models\User;
use App\Models\DocumentRecipient;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreCompanyFolderRequest;
use App\Http\Requests\StoreCompanyFileRequest;

class CompanyDokumenController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $userId = $user->id;
        $activeCompanyId = session('active_company_id');

        if (!$activeCompanyId) {
            return redirect()->route('dashboard')->with('error', 'Tidak ada company aktif');
        }

        // ================================
        // CEK ROLE COMPANY
        // ================================
        $userCompany = $user->userCompanies()
            ->where('company_id', $activeCompanyId)
            ->with('role')
            ->first();

        $companyRole = $userCompany?->role?->name ?? 'Member';
        $isSuperAdmin = ($companyRole === 'SuperAdmin');

        // ================================
        // ACCESS CONDITION FOR FOLDERS
        // ================================
        $folderAccess = function ($query) use ($userId, $isSuperAdmin) {
            $query->where('is_private', false);

            if ($isSuperAdmin) {
                $query->orWhere('is_private', true);
            }

            $query->orWhere(function ($q) use ($userId) {
                $q->where('is_private', true)
                    ->where('created_by', $userId);
            });

            $query->orWhere(function ($q) use ($userId) {
                $q->where('is_private', true)
                    ->whereHas('documentRecipients', function ($qr) use ($userId) {
                        $qr->where('user_id', $userId)
                            ->where('status', true);
                    });
            });
        };

        // ================================
        // ACCESS CONDITION FOR FILES
        // ================================
        $fileAccess = function ($query) use ($userId, $isSuperAdmin) {
            $query->where('is_private', false);

            if ($isSuperAdmin) {
                $query->orWhere('is_private', true);
            }

            $query->orWhere(function ($q) use ($userId) {
                $q->where('is_private', true)
                    ->where('uploaded_by', $userId);
            });

            $query->orWhere(function ($q) use ($userId) {
                $q->where('is_private', true)
                    ->whereHas('documentRecipients', function ($qr) use ($userId) {
                        $qr->where('user_id', $userId)
                            ->where('status', true);
                    });
            });
        };

        // ================================
        // GET FOLDERS (PER COMPANY)
        // ================================
        $folders = Folder::where('company_id', $activeCompanyId) // ← BEDANYA DI SINI
            ->whereNull('workspace_id') // ← PASTIKAN BUKAN MILIK WORKSPACE
            ->where($folderAccess)
            ->with([
                'creator',
                'documentRecipients',
                'files' => function ($query) use ($fileAccess) {
                    $query->where($fileAccess)
                        ->with('uploader', 'documentRecipients');
                }
            ])
            ->withCount([
                'files' => function ($query) use ($fileAccess) {
                    $query->where($fileAccess);
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // ================================
        // GET ROOT FILES (PER COMPANY)
        // ================================
        $rootFiles = File::where('company_id', $activeCompanyId) // ← BEDANYA DI SINI
            ->whereNull('workspace_id') // ← PASTIKAN BUKAN MILIK WORKSPACE
            ->whereNull('folder_id')
            ->where($fileAccess)
            ->with(['uploader', 'documentRecipients'])
            ->orderBy('uploaded_at', 'desc')
            ->get();

        $company = Company::find($activeCompanyId);

        return view('company-dokumen-dan-file', compact('company', 'folders', 'rootFiles'));
    }

    public function storeFolder(StoreCompanyFolderRequest $request)
    {
        \Log::info('📝 storeFolder request data:', $request->all());

        $validated = $request->validated();

        \Log::info('✅ Validated data:', $validated);

        // ✅ HANYA 1x CREATE
        Folder::create([
            'company_id' => $validated['company_id'],
            'workspace_id' => null,
            'name' => $validated['name'],
            'is_private' => $validated['is_private'] ?? false,
            'created_by' => auth()->id(),
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        $redirectUrl = route('company-documents.index');

        if ($validated['parent_id'] ?? null) {
            $redirectUrl .= '?folder=' . $validated['parent_id'];
        }

        \Log::info('🔀 Redirect URL generated:', ['url' => $redirectUrl]);

        return response()->json([
            'success' => true,
            'redirect_url' => $redirectUrl,
            'alert' => [
                'icon' => 'success',
                'title' => 'Folder berhasil dibuat!',
                'text' => 'Data folder baru sudah tersimpan.',
            ]
        ]);
    }

    public function storeFile(StoreCompanyFileRequest $request)
    {
        $validated = $request->validated();

        $uploaded = $request->file('file');
        $originalName = pathinfo($uploaded->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $uploaded->getClientOriginalExtension();

        $companyId = $validated['company_id'];
        $folderId = $validated['folder_id'] ?? null;

        // Generate nama unik
        $newName = $originalName;
        $counter = 1;

        while (
            File::where('company_id', $companyId)
                ->where('folder_id', $folderId)
                ->where('file_name', $newName . '.' . $extension)
                ->exists()
        ) {
            $newName = $originalName . '(' . $counter . ')';
            $counter++;
        }

        $finalName = $newName . '.' . $extension;

        // Simpan fisik
        $path = $uploaded->storeAs('files', $finalName, 'public');

        // Simpan ke database
        $fileModel = File::create([
            'company_id' => $companyId,
            'workspace_id' => null, // ← PENTING: NULL untuk company-level
            'folder_id' => $folderId,
            'uploaded_by' => auth()->id(),
            'file_name' => $finalName,
            'file_path' => $path,
            'file_size' => $uploaded->getSize(),
            'file_type' => $extension,
            'file_url' => asset('storage/' . $path),
            'is_private' => false,
            'uploaded_at' => now(),
        ]);

        $redirectUrl = route('company-documents.index');

        if ($folderId) {
            $redirectUrl .= '?folder=' . $folderId;
        }

        return response()->json([
            'success' => true,
            'redirect_url' => $redirectUrl,
            'alert' => [
                'icon' => 'success',
                'title' => 'File berhasil diunggah!',
                'text' => 'File ' . $fileModel->file_name . ' sudah tersimpan.',
            ]
        ]);
    }

    public function updateFolder(Request $request, $id)
    {
        $folder = Folder::findOrFail($id);

        $oldName = $folder->name;
        $oldPrivate = $folder->is_private;

        $newName = $request->name;
        $newPrivate = $request->is_private == 1;

        $messages = [];

        if ($oldName !== $newName) {
            $messages[] = "Nama folder diperbarui.";
        }

        if ($oldPrivate !== $newPrivate) {
            $messages[] = "Aturan privasi folder diperbarui.";

            if ($oldPrivate === true && $newPrivate === false) {
                $folder->documentRecipients()->update(['status' => false]);
            }
        }

        if (empty($messages)) {
            $messages[] = "Tidak ada perubahan pada folder.";
        }

        $folder->update([
            'name' => $newName,
            'is_private' => $newPrivate,
        ]);

        $redirectUrl = route('company-documents.index');
        $redirectUrl .= '?folder=' . $folder->id;

        return response()->json([
            'success' => true,
            'redirect_url' => $redirectUrl,
            'alert' => [
                'icon' => 'success',
                'title' => 'Folder berhasil diperbarui!',
                'text' => implode(" ", $messages),
            ]
        ]);
    }

    public function updateFile(Request $request, $id)
    {
        $file = File::findOrFail($id);

        $oldName = $file->name;
        $oldPrivate = $file->is_private;

        $newName = $request->name;
        $newPrivate = $request->is_private == 1;

        $messages = [];

        if ($oldName !== $newName) {
            $messages[] = "Nama file diperbarui.";
        }

        if ($oldPrivate !== $newPrivate) {
            $messages[] = "Aturan privasi file diperbarui.";

            if ($oldPrivate === true && $newPrivate === false) {
                $file->documentRecipients()->update(['status' => false]);
            }
        }

        if (empty($messages)) {
            $messages[] = "Tidak ada perubahan pada file.";
        }

        $file->update([
            'name' => $newName,
            'is_private' => $newPrivate,
        ]);

        $redirectUrl = route('company-documents.index');

        if ($file->folder_id) {
            $redirectUrl .= '?folder=' . $file->folder_id;
        }

        return response()->json([
            'success' => true,
            'redirect_url' => $redirectUrl,
            'alert' => [
                'icon' => 'success',
                'title' => 'File berhasil diperbarui!',
                'text' => implode(" ", $messages),
            ]
        ]);
    }

    public function destroyFile($id)
    {
        $file = File::findOrFail($id);

        if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
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
        $companyId = $folder->company_id;
        $parentId = $folder->parent_id;

        $folder->delete();

        $url = route('company-documents.index');
        if ($parentId) {
            $url .= '?folder=' . $parentId;
        }

        return redirect($url)->with('alert', [
            'icon' => 'success',
            'title' => 'Folder dihapus',
            'text' => 'Folder berhasil dihapus.'
        ]);
    }

    public function getCompanyMembers(Request $req)
    {
        $user = Auth::user();
        $activeCompanyId = session('active_company_id');

        $userCompany = $user->userCompanies()
            ->where('company_id', $activeCompanyId)
            ->with('role')
            ->first();

        $companyRole = $userCompany?->role?->name ?? 'Member';
        $isHighRole = in_array($companyRole, ['SuperAdmin', 'Admin']);

        $folderCreatedBy = $req->folder_created_by ?: null;
        $fileUploadedBy = $req->file_uploaded_by ?: null;

        if (!$isHighRole) {
            if ($folderCreatedBy != $user->id && $fileUploadedBy != $user->id) {
                return response()->json([
                    'error' => 'Anda tidak memiliki akses untuk melihat anggota company ini'
                ], 403);
            }
        }

        $members = User::whereHas('userCompanies', function ($q) use ($activeCompanyId) {
            $q->where('company_id', $activeCompanyId);
        })->get();

        $filtered = $members->map(function ($m) use ($folderCreatedBy, $fileUploadedBy, $isHighRole, $user) {
            $canBeSelected = false;

            if ($isHighRole) {
                $canBeSelected = true;
            }

            if ($folderCreatedBy && $m->id == $folderCreatedBy) {
                $canBeSelected = true;
            }

            if ($fileUploadedBy && $m->id == $fileUploadedBy) {
                $canBeSelected = true;
            }

            return [
                'id' => $m->id,
                'name' => $m->full_name ?? $m->name,
                'avatar' => $m->avatar ?? 'https://i.pravatar.cc/150?img=' . rand(1, 70),
                'can_be_selected' => $canBeSelected,
                'selected' => false,
            ];
        });

        return response()->json(['members' => $filtered]);
    }

    public function recipientsStore(Request $request)
    {
        $request->validate([
            'document_id' => 'required|uuid',
            'selected_members' => 'required|json',
        ]);

        $selectedMembers = json_decode($request->selected_members, true) ?? [];

        DocumentRecipient::where('document_id', $request->document_id)
            ->whereNotIn('user_id', $selectedMembers)
            ->update(['status' => false]);

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

        $hasActiveRecipients = DocumentRecipient::where('document_id', $request->document_id)
            ->where('status', true)
            ->exists();

        if ($file = File::find($request->document_id)) {
            if ($hasActiveRecipients) {
                $file->update(['is_private' => true]);
            }
        } elseif ($folder = Folder::find($request->document_id)) {
            if ($hasActiveRecipients) {
                $folder->update(['is_private' => true]);
            }
        }

        return redirect()->back()->with('alert', [
            'icon' => 'success',
            'title' => 'Berhasil!',
            'text' => 'Penerima berkas berhasil diperbarui.',
        ])->with('alert_once', true);
    }

    public function getRecipients($documentId)
    {
        $recipients = DocumentRecipient::where('document_id', $documentId)
            ->where('status', true)
            ->pluck('user_id');

        return response()->json(['recipients' => $recipients]);
    }
}