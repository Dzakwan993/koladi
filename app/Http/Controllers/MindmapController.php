<?php

namespace App\Http\Controllers;

use App\Models\Mindmap;
use App\Models\MindmapNode;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MindmapController extends Controller
{
    // Helper method untuk cek akses workspace
    private function checkWorkspaceAccess($workspaceId)
    {
        $workspace = Workspace::findOrFail($workspaceId);
        
        // Cek apakah user adalah member dari workspace
        $isMember = $workspace->members()
            ->where('user_id', Auth::id())
            ->exists();
        
        if (!$isMember) {
            abort(403, 'Anda tidak memiliki akses ke workspace ini');
        }
        
        return $workspace;
    }

    // Tampilkan halaman mindmap berdasarkan workspace
    public function index($workspaceId)
    {
        // Cek akses workspace
        $workspace = $this->checkWorkspaceAccess($workspaceId);

        // Ambil atau buat mindmap untuk workspace
        $mindmap = Mindmap::firstOrCreate(
            ['workspace_id' => $workspaceId],
            ['title' => 'Mind Map ' . $workspace->name]
        );

        // Jika mindmap baru dibuat, buat root node
        if ($mindmap->nodes()->count() === 0) {
            MindmapNode::create([
                'mindmap_id' => $mindmap->id,
                'title' => $workspace->name,
                'description' => 'Node utama mind map',
                'x' => 400,
                'y' => 300,
                'is_root' => true,
                'type' => 'default'
            ]);
        }

        // Load nodes dengan relasi parent dan children
        $mindmap->load('nodes.parent', 'nodes.children');

        return view('mindmap', compact('mindmap', 'workspace'));
    }

    // Get data mindmap (untuk AJAX)
    public function getData($workspaceId)
    {
        // Cek akses workspace
        $this->checkWorkspaceAccess($workspaceId);

        $mindmap = Mindmap::where('workspace_id', $workspaceId)
            ->with(['nodes.parent', 'nodes.children'])
            ->firstOrFail();

        return response()->json($mindmap);
    }

    // Tambah node baru
    public function storeNode(Request $request, $workspaceId)
    {
        // Cek akses workspace
        $this->checkWorkspaceAccess($workspaceId);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'x' => 'required|numeric',
            'y' => 'required|numeric',
            'type' => 'required|in:default,idea,task',
            'parent_id' => 'nullable|exists:mindmap_nodes,id',
            'connection_side' => 'required|in:auto,top,right,bottom,left',
        ]);

        $mindmap = Mindmap::where('workspace_id', $workspaceId)->firstOrFail();

        // Validasi parent_id harus milik mindmap yang sama
        if ($validated['parent_id']) {
            $parent = MindmapNode::where('id', $validated['parent_id'])
                ->where('mindmap_id', $mindmap->id)
                ->first();
            
            if (!$parent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parent node tidak valid'
                ], 400);
            }
        }

        $node = MindmapNode::create([
            'mindmap_id' => $mindmap->id,
            'created_by' => Auth::id(), // Track siapa yang buat node
            ...$validated
        ]);

        // Load relasi untuk response
        $node->load('parent', 'children', 'creator');

        return response()->json([
            'success' => true,
            'node' => $node
        ]);
    }

    // Update node
    public function updateNode(Request $request, $workspaceId, $id)
    {
        // Cek akses workspace
        $this->checkWorkspaceAccess($workspaceId);

        $node = MindmapNode::findOrFail($id);

        // Cek apakah node milik workspace ini
        if ($node->mindmap->workspace_id != $workspaceId) {
            abort(403, 'Node tidak ditemukan di workspace ini');
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'x' => 'sometimes|required|numeric',
            'y' => 'sometimes|required|numeric',
            'type' => 'sometimes|required|in:default,idea,task',
            'parent_id' => 'nullable|exists:mindmap_nodes,id',
            'connection_side' => 'sometimes|required|in:auto,top,right,bottom,left',
        ]);

        // Validasi parent_id tidak boleh diri sendiri atau descendants
        if (isset($validated['parent_id']) && $validated['parent_id']) {
            if ($validated['parent_id'] == $node->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Node tidak dapat menjadi parent dari dirinya sendiri'
                ], 400);
            }

            // Cek parent_id harus dari mindmap yang sama
            $parent = MindmapNode::where('id', $validated['parent_id'])
                ->where('mindmap_id', $node->mindmap_id)
                ->first();
            
            if (!$parent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parent node tidak valid'
                ], 400);
            }
        }

        $node->update($validated);
        $node->load('parent', 'children', 'creator');

        return response()->json([
            'success' => true,
            'node' => $node
        ]);
    }

    // Update posisi node (untuk drag)
    public function updatePosition(Request $request, $workspaceId, $id)
    {
        // Cek akses workspace
        $this->checkWorkspaceAccess($workspaceId);

        $node = MindmapNode::findOrFail($id);

        // Cek apakah node milik workspace ini
        if ($node->mindmap->workspace_id != $workspaceId) {
            abort(403, 'Node tidak ditemukan di workspace ini');
        }

        $validated = $request->validate([
            'x' => 'required|numeric',
            'y' => 'required|numeric',
        ]);

        $node->update($validated);

        return response()->json([
            'success' => true,
            'node' => $node
        ]);
    }

    // Hapus node
    public function deleteNode($workspaceId, $id)
    {
        // Cek akses workspace
        $this->checkWorkspaceAccess($workspaceId);

        $node = MindmapNode::findOrFail($id);

        // Cek apakah node milik workspace ini
        if ($node->mindmap->workspace_id != $workspaceId) {
            abort(403, 'Node tidak ditemukan di workspace ini');
        }

        // Cek jangan hapus root
        if ($node->is_root) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus node utama'
            ], 400);
        }

        $node->delete(); // Children otomatis kehapus (CASCADE)

        return response()->json(['success' => true]);
    }

    // Batch update positions (untuk drag multiple nodes)
    public function batchUpdatePositions(Request $request, $workspaceId)
    {
        // Cek akses workspace
        $this->checkWorkspaceAccess($workspaceId);

        $validated = $request->validate([
            'nodes' => 'required|array',
            'nodes.*.id' => 'required|exists:mindmap_nodes,id',
            'nodes.*.x' => 'required|numeric',
            'nodes.*.y' => 'required|numeric',
        ]);

        $mindmap = Mindmap::where('workspace_id', $workspaceId)->firstOrFail();
        
        foreach ($validated['nodes'] as $nodeData) {
            $node = MindmapNode::where('id', $nodeData['id'])
                ->where('mindmap_id', $mindmap->id)
                ->first();
            
            if ($node) {
                $node->update([
                    'x' => $nodeData['x'],
                    'y' => $nodeData['y']
                ]);
            }
        }

        return response()->json(['success' => true]);
    }
}