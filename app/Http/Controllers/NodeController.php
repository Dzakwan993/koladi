<?php

namespace App\Http\Controllers;

use App\Models\Node;
use Illuminate\Http\Request;

class NodeController extends Controller
{
    // Get semua node berdasarkan mindmap_id
    public function index($mindmap_id)
    {
        $nodes = Node::where('mindmap_id', $mindmap_id)->get();
        return response()->json($nodes);
    }

    // Tambah node baru
    public function store(Request $request)
    {
        $node = Node::create([
            'mindmap_id' => $request->mindmap_id,
            'parent_id' => $request->parent_id,
            'title' => $request->title,
            'description' => $request->description,
            'x' => $request->x,
            'y' => $request->y,
            'type' => $request->type ?? 'default',
            'connection_side' => $request->connection_side ?? 'auto'
        ]);

        return response()->json($node);
    }

    // Update node (judul, posisi, dll.)
    public function update(Request $request, $id)
    {
        $node = Node::findOrFail($id);
        $node->update($request->all());

        return response()->json($node);
    }

    // Hapus node
    public function destroy($id)
    {
        $node = Node::findOrFail($id);
        $node->delete();

        return response()->json(['message' => 'Node deleted']);
    }
}
