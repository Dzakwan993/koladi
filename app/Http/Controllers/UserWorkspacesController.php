<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Http\Request;

class UserWorkspacesController extends Controller
{
    public function show($workspaceId)
    {
        // Ambil semua pengumuman untuk workspace ini
        $pengumumans = Pengumuman::where('workspace_id', $workspaceId)->latest()->get();

        return view('workspace', compact('pengumumans', 'workspaceId'));
    }
}
