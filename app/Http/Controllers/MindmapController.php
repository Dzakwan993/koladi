<?php

namespace App\Http\Controllers;

use App\Models\Mindmap;
use App\Models\MindmapNode;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MindmapController extends Controller
{
    public function index()
{
    // Cari workspace pertama yang ada di database
    $workspace = Workspace::first();

    if (!$workspace) {
        abort(404, 'Tidak ada workspace yang tersedia. Silakan buat workspace terlebih dahulu.');
    }

    // Cari atau buat mindmap untuk workspace ini
    $mindmap = Mindmap::firstOrCreate(
        ['workspace_id' => $workspace->id],
        [
            'id' => Str::uuid(),
            'title' => 'Mind Map Utama',
            'description' => 'Mind map utama Anda'
        ]
    );

    return view('mindmap', compact('workspace', 'mindmap'));
}
    public function show($workspaceId)
    {
        $workspace = Workspace::findOrFail($workspaceId);

        // Cari atau buat mindmap default untuk workspace
        $mindmap = Mindmap::firstOrCreate(
            ['workspace_id' => $workspaceId],
            [
                'id' => Str::uuid(),
                'title' => 'Mind Map ' . $workspace->name,
                'description' => 'Mind map untuk workspace ' . $workspace->name
            ]
        );

        return view('mindmap', compact('workspace', 'mindmap'));
    }

    public function getMindmapData($mindmapId)
    {
        $mindmap = Mindmap::with(['nodes' => function ($query) {
            $query->orderBy('sort_order');
        }])->findOrFail($mindmapId);

        return response()->json([
            'nodes' => $mindmap->nodes->map(function ($node) {
                return [
                    'id' => $node->id,
                    'title' => $node->title,
                    'description' => $node->description,
                    'x' => (float) $node->x_position,
                    'y' => (float) $node->y_position,
                    'isRoot' => $node->parent_id === null,
                    'type' => $node->type,
                    'parentId' => $node->parent_id,
                    'connectionSide' => $node->connection_side
                ];
            })
        ]);
    }

    public function saveNode(Request $request, $mindmapId)
    {
        $request->validate([
            'nodes' => 'required|array',
            'nodes.*.id' => 'sometimes|string',
            'nodes.*.title' => 'required|string|max:255',
            'nodes.*.description' => 'nullable|string',
            'nodes.*.x' => 'required|numeric',
            'nodes.*.y' => 'required|numeric',
            'nodes.*.parentId' => 'nullable|string',
            'nodes.*.type' => 'required|string',
            'nodes.*.connectionSide' => 'required|string'
        ]);

        DB::transaction(function () use ($request, $mindmapId) {
            $existingNodeIds = [];

            foreach ($request->nodes as $index => $nodeData) {
                $node = MindmapNode::updateOrCreate(
                    [
                        'id' => $nodeData['id'] ?? Str::uuid(),
                    ],
                    [
                        'mindmap_id' => $mindmapId,
                        'title' => $nodeData['title'],
                        'description' => $nodeData['description'] ?? '',
                        'x_position' => $nodeData['x'],
                        'y_position' => $nodeData['y'],
                        'parent_id' => $nodeData['parentId'],
                        'type' => $nodeData['type'],
                        'connection_side' => $nodeData['connectionSide'],
                        'sort_order' => $index
                    ]
                );

                $existingNodeIds[] = $node->id;
            }

            // Hapus node yang tidak ada dalam request
            MindmapNode::where('mindmap_id', $mindmapId)
                ->whereNotIn('id', $existingNodeIds)
                ->delete();
        });

        // Broadcast ke Pusher
        $this->broadcastMindmapUpdate($mindmapId);

        return response()->json(['message' => 'Mindmap saved successfully']);
    }

    public function addNode(Request $request, $mindmapId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'x' => 'required|numeric',
            'y' => 'required|numeric',
            'parent_id' => 'nullable|string|exists:mindmap_nodes,id',
            'type' => 'required|string',
            'connection_side' => 'required|string'
        ]);

        $node = MindmapNode::create([
            'id' => Str::uuid(),
            'mindmap_id' => $mindmapId,
            'parent_id' => $request->parent_id,
            'title' => $request->title,
            'description' => $request->description,
            'x_position' => $request->x,
            'y_position' => $request->y,
            'type' => $request->type,
            'connection_side' => $request->connection_side,
            'sort_order' => MindmapNode::where('mindmap_id', $mindmapId)->count()
        ]);

        // Broadcast ke Pusher
        $this->broadcastMindmapUpdate($mindmapId);

        return response()->json([
            'message' => 'Node added successfully',
            'node' => $node
        ]);
    }

    public function updateNode(Request $request, $nodeId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'x' => 'required|numeric',
            'y' => 'required|numeric',
            'parent_id' => 'nullable|string|exists:mindmap_nodes,id',
            'type' => 'required|string',
            'connection_side' => 'required|string'
        ]);

        $node = MindmapNode::findOrFail($nodeId);

        $node->update([
            'title' => $request->title,
            'description' => $request->description,
            'x_position' => $request->x,
            'y_position' => $request->y,
            'parent_id' => $request->parent_id,
            'type' => $request->type,
            'connection_side' => $request->connection_side
        ]);

        // Broadcast ke Pusher
        $this->broadcastMindmapUpdate($node->mindmap_id);

        return response()->json([
            'message' => 'Node updated successfully',
            'node' => $node
        ]);
    }

    public function deleteNode($nodeId)
    {
        $node = MindmapNode::findOrFail($nodeId);
        $mindmapId = $node->mindmap_id;

        // Hapus node dan semua child nodes secara recursive
        $this->deleteNodeRecursive($node);

        // Broadcast ke Pusher
        $this->broadcastMindmapUpdate($mindmapId);

        return response()->json(['message' => 'Node deleted successfully']);
    }

    private function deleteNodeRecursive($node)
    {
        // Hapus semua child nodes terlebih dahulu
        foreach ($node->children as $child) {
            $this->deleteNodeRecursive($child);
        }

        // Hapus node itu sendiri
        $node->delete();
    }

    private function broadcastMindmapUpdate($mindmapId)
    {
        // Broadcast update ke Pusher
        if (class_exists('Pusher\Pusher')) {
            $pusher = new \Pusher\Pusher(
                config('broadcasting.connections.pusher.key'),
                config('broadcasting.connections.pusher.secret'),
                config('broadcasting.connections.pusher.app_id'),
                config('broadcasting.connections.pusher.options')
            );

            $pusher->trigger("mindmap-{$mindmapId}", 'mindmap-updated', [
                'message' => 'Mindmap updated',
                'timestamp' => now()
            ]);
        }
    }

    public function getData($id)
{
    try {
        $mindmap = Mindmap::with('nodes')->findOrFail($id);

        $nodes = $mindmap->nodes->map(function ($node) {
            return [
                'id' => $node->id,
                'title' => $node->title,
                'description' => $node->description,
                'x' => (float) $node->x_position,
                'y' => (float) $node->y_position,
                'isRoot' => $node->parent_id === null,
                'type' => $node->type,
                'parentId' => $node->parent_id,
                'connectionSide' => $node->connection_side
            ];
        });

        return response()->json([
            'nodes' => $nodes
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'nodes' => []
        ], 500);
    }
}

public function save($id, Request $request)
{
    // GANTI: \Log::info dengan logger()
    logger('Save mindmap request received', [
        'mindmap_id' => $id,
        'nodes_count' => count($request->nodes ?? []),
        'nodes_data' => $request->nodes
    ]);

    try {
        DB::beginTransaction();

        $existingNodeIds = [];

        foreach ($request->nodes as $index => $nodeData) {
            // Generate UUID baru untuk node yang belum ada di database
            $nodeId = $nodeData['id'] ?? Str::uuid();

            // Jika ID adalah number, convert ke string dan generate UUID baru
            if (is_numeric($nodeId)) {
                $nodeId = Str::uuid();
            }

            $node = MindmapNode::updateOrCreate(
                [
                    'id' => $nodeId,
                ],
                [
                    'mindmap_id' => $id,
                    'title' => $nodeData['title'] ?? 'Untitled',
                    'description' => $nodeData['description'] ?? '',
                    'x_position' => $nodeData['x'] ?? 0,
                    'y_position' => $nodeData['y'] ?? 0,
                    'parent_id' => $nodeData['parentId'] ?? null,
                    'type' => $nodeData['type'] ?? 'default',
                    'connection_side' => $nodeData['connectionSide'] ?? 'auto',
                    'sort_order' => $index
                ]
            );

            $existingNodeIds[] = $node->id;
        }

        // Hapus node yang tidak ada dalam request
        MindmapNode::where('mindmap_id', $id)
            ->whereNotIn('id', $existingNodeIds)
            ->delete();

        DB::commit();

        // GANTI: \Log::info dengan logger()
        logger('Mindmap saved successfully', ['mindmap_id' => $id]);

        // Broadcast ke Pusher
        $this->broadcastMindmapUpdate($id);

        return response()->json([
            'success' => true,
            'message' => 'Mindmap saved successfully'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        // GANTI: \Log::error dengan logger() atau report()
        logger('Failed to save mindmap: ' . $e->getMessage(), [
            'mindmap_id' => $id,
            'exception' => $e
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to save mindmap: ' . $e->getMessage()
        ], 500);
    }
}

}
