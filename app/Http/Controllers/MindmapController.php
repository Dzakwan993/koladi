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
        $workspace = Workspace::first();

        if (!$workspace) {
            abort(404, 'Tidak ada workspace yang tersedia. Silakan buat workspace terlebih dahulu.');
        }

        return $this->loadMindmapByWorkspace($workspace);
    }

    private function loadMindmapByWorkspace(Workspace $workspace)
    {
        $mindmap = Mindmap::firstOrCreate(
            ['workspace_id' => $workspace->id],
            [
                'id' => Str::uuid()->toString(),
                'title' => 'Mind Map ' . $workspace->name,
                'description' => 'Mind map untuk workspace ' . $workspace->name
            ]
        );

        return view('mindmap', compact('workspace', 'mindmap'));
    }

    public function show($workspaceId)
    {
        $workspace = Workspace::findOrFail($workspaceId);

        $mindmap = Mindmap::firstOrCreate(
            ['workspace_id' => $workspaceId],
            [
                'id' => Str::uuid()->toString(),
                'title' => 'Mind Map ' . $workspace->name,
                'description' => 'Mind map untuk workspace ' . $workspace->name
            ]
        );

        return view('mindmap', compact('workspace', 'mindmap'));
    }

    public function getData($id)
    {
        try {
            $mindmap = Mindmap::with(['nodes' => function ($query) {
                $query->orderBy('sort_order');
            }])->findOrFail($id);

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
                    'connectionSide' => $node->connection_side ?: 'auto:auto',
                ];
            });

            return response()->json([
                'nodes' => $nodes
            ]);
        } catch (\Exception $e) {
            logger('Failed to get mindmap data: ' . $e->getMessage(), [
                'mindmap_id' => $id,
                'exception' => $e
            ]);

            return response()->json([
                'nodes' => []
            ], 500);
        }
    }

    public function save($id, Request $request)
    {
        $request->validate([
            'nodes' => 'required|array',
            'nodes.*.id' => 'nullable|string',
            'nodes.*.title' => 'required|string|max:255',
            'nodes.*.description' => 'nullable|string',
            'nodes.*.x' => 'required|numeric',
            'nodes.*.y' => 'required|numeric',
            'nodes.*.parentId' => 'nullable|string',
            'nodes.*.type' => 'required|string|max:50',
            'nodes.*.connectionSide' => 'nullable|string|max:50',
        ]);

        try {
            DB::beginTransaction();

            $mindmap = Mindmap::findOrFail($id);
            $existingNodeIds = [];

            foreach ($request->nodes as $index => $nodeData) {
                $nodeId = $nodeData['id'] ?? Str::uuid()->toString();

                if (is_numeric($nodeId)) {
                    $nodeId = Str::uuid()->toString();
                }

                $node = MindmapNode::updateOrCreate(
                    ['id' => $nodeId],
                    [
                        'mindmap_id' => $mindmap->id,
                        'title' => $nodeData['title'] ?? 'Untitled',
                        'description' => $nodeData['description'] ?? '',
                        'x_position' => $nodeData['x'] ?? 0,
                        'y_position' => $nodeData['y'] ?? 0,
                        'parent_id' => $nodeData['parentId'] ?? null,
                        'type' => $nodeData['type'] ?? 'default',
                        'connection_side' => $nodeData['connectionSide'] ?? 'auto:auto',
                        'sort_order' => $index,
                    ]
                );

                $existingNodeIds[] = $node->id;
            }

            MindmapNode::where('mindmap_id', $mindmap->id)
                ->whereNotIn('id', $existingNodeIds)
                ->delete();

            DB::commit();

            $this->broadcastMindmapUpdate($mindmap->id);

            return response()->json([
                'success' => true,
                'message' => 'Mindmap saved successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

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

    public function deleteNode($nodeId)
    {
        $node = MindmapNode::findOrFail($nodeId);
        $mindmapId = $node->mindmap_id;

        $this->deleteNodeRecursive($node);

        $this->broadcastMindmapUpdate($mindmapId);

        return response()->json([
            'message' => 'Node deleted successfully'
        ]);
    }

    private function deleteNodeRecursive($node)
    {
        $node->load('children');

        foreach ($node->children as $child) {
            $this->deleteNodeRecursive($child);
        }

        $node->delete();
    }

    private function broadcastMindmapUpdate($mindmapId)
    {
        if (class_exists('Pusher\Pusher')) {
            try {
                $pusher = new \Pusher\Pusher(
                    config('broadcasting.connections.pusher.key'),
                    config('broadcasting.connections.pusher.secret'),
                    config('broadcasting.connections.pusher.app_id'),
                    config('broadcasting.connections.pusher.options')
                );

                $pusher->trigger("mindmap-{$mindmapId}", 'mindmap-updated', [
                    'message' => 'Mindmap updated',
                    'timestamp' => now()->toDateTimeString()
                ]);
            } catch (\Throwable $e) {
                logger('Pusher broadcast failed: ' . $e->getMessage(), [
                    'mindmap_id' => $mindmapId
                ]);
            }
        }
    }
}
