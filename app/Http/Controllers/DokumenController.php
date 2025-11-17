<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\Folder;
use App\Models\File;
use Illuminate\Http\Request;
use App\Http\Requests\StoreFolderRequest;

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
            ->whereNull('folder_id')
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

    
}