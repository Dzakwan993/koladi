<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFolderRequest;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FolderController extends Controller
{
    // Menampilkan daftar folder
    public function index(Request $request)
    {
        $folders = Folder::where('workspace_id', session('active_workspace_id'))
            ->latest()
            ->get();

        // Kalau request dari AJAX → return JSON
        if ($request->ajax()) {
            return response()->json($folders);
        }

        // Kalau bukan AJAX → render view biasa
        return view('folders.index', compact('folders'));
    }

    // Buat folder baru
    public function store(StoreFolderRequest $request)
    {
        $folder = Folder::create([
            'workspace_id' => $request->workspace_id,
            'name' => $request->name,
            'is_private' => $request->boolean('is_private'),
            'created_by' => Auth::id(),
        ]);

        // Kalau request dari AJAX → return JSON respons sukses
        if ($request->ajax()) {
            return response()->json([
                'message' => 'Folder berhasil dibuat!',
                'folder' => $folder
            ]);
        }

        // Kalau bukan AJAX → redirect balik ke halaman dengan flash message
        return redirect()->back()->with('success', 'Folder berhasil dibuat!');
    }
}
