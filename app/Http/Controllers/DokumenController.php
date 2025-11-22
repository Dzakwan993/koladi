<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\Folder;
use App\Models\File;
use Illuminate\Http\Request;
use App\Http\Requests\StoreFolderRequest;
use App\Http\Requests\StoreFileRequest;

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








}