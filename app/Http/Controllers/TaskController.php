<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BoardColumn;
use App\Models\Workspace;
use App\Models\UserWorkspace;
use Illuminate\Support\Str;


class TaskController extends Controller
{
    // ✅ FIXED: Dapatkan kolom kanban HANYA untuk workspace yang diminta
    public function getBoardColumns($workspaceId)
    {
        try {
            $user = Auth::user();
            
            // Validasi: user harus memiliki akses ke workspace ini
            $userWorkspace = UserWorkspace::where('user_id', $user->id)
                ->where('workspace_id', $workspaceId)
                ->first();

            if (!$userWorkspace) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke workspace ini'
                ], 403);
            }

            $workspace = Workspace::find($workspaceId);
            if (!$workspace) {
                return response()->json([
                    'success' => false,
                    'message' => 'Workspace tidak ditemukan'
                ], 404);
            }

            // ✅ FIXED: HANYA ambil columns dari workspace ini saja
            $columns = BoardColumn::where('workspace_id', $workspaceId)
                ->orderBy('position')
                ->get();

            // ✅ DEBUG: Log untuk memastikan query benar
            \Log::info("Board columns for workspace {$workspaceId}: " . $columns->count() . " columns found");
            foreach ($columns as $col) {
                \Log::info(" - Column: {$col->name} (ID: {$col->id}, Workspace: {$col->workspace_id})");
            }

            return response()->json([
                'success' => true,
                'columns' => $columns,
                'workspace_name' => $workspace->name,
                'workspace_id' => $workspace->id,
                'debug_info' => [
                    'requested_workspace_id' => $workspaceId,
                    'columns_count' => $columns->count(),
                    'columns_workspace_ids' => $columns->pluck('workspace_id')->unique()->toArray()
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting board columns: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kolom: ' . $e->getMessage()
            ], 500);
        }
    }

    // ✅ FIXED: Buat kolom baru - PASTIKAN hanya untuk workspace ini
    public function createBoardColumn(Request $request)
    {
        $request->validate([
            'workspace_id' => 'required|exists:workspaces,id',
            'name' => 'required|string|max:255'
        ]);

        try {
            $user = Auth::user();
            
            // Validasi akses user ke workspace ini
            $userWorkspace = UserWorkspace::where('user_id', $user->id)
                ->where('workspace_id', $request->workspace_id)
                ->first();

            if (!$userWorkspace) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke workspace ini'
                ], 403);
            }

            // ✅ FIXED: Validasi workspace termasuk dalam company yang aktif
            $workspace = Workspace::find($request->workspace_id);
            $activeCompanyId = session('active_company_id');
            
            if ($workspace->company_id !== $activeCompanyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Workspace tidak termasuk dalam perusahaan yang aktif'
                ], 403);
            }

            // ✅ FIXED: HANYA hitung posisi dari workspace yang sama
            $lastPosition = BoardColumn::where('workspace_id', $request->workspace_id)
                ->max('position');

            $column = BoardColumn::create([
                'id' => Str::uuid()->toString(),
                'workspace_id' => $request->workspace_id, // ✅ PASTIKAN workspace_id benar
                'name' => $request->name,
                'position' => ($lastPosition ? $lastPosition + 1 : 1),
                'created_by' => $user->id
            ]);

            // ✅ DEBUG: Log pembuatan kolom
            \Log::info("New column created: {$column->name} for workspace {$request->workspace_id}");

            return response()->json([
                'success' => true,
                'message' => 'Kolom berhasil ditambahkan',
                'column' => $column,
                'debug_info' => [
                    'workspace_id' => $request->workspace_id,
                    'workspace_name' => $workspace->name,
                    'company_id' => $workspace->company_id
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error creating board column: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan kolom: ' . $e->getMessage()
            ], 500);
        }
    }

    // ✅ FIXED: Hapus kolom - Validasi kepemilikan workspace
    public function deleteBoardColumn($columnId)
    {
        try {
            $user = Auth::user();
            $column = BoardColumn::with('workspace')->findOrFail($columnId);

            // Validasi akses user ke workspace kolom ini
            $userWorkspace = UserWorkspace::where('user_id', $user->id)
                ->where('workspace_id', $column->workspace_id)
                ->first();

            if (!$userWorkspace) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke workspace ini'
                ], 403);
            }

            // Cek apakah kolom default
            $defaultColumns = ['To Do List', 'Dikerjakan', 'Selesai', 'Batal'];
            if (in_array($column->name, $defaultColumns)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kolom default tidak dapat dihapus'
                ], 400);
            }

            $column->delete();

            \Log::info("Column deleted: {$column->name} from workspace {$column->workspace_id}");

            return response()->json([
                'success' => true,
                'message' => 'Kolom berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting board column: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kolom: ' . $e->getMessage()
            ], 500);
        }
    }

    // Update posisi kolom - VALIDASI WORKSPACE ACCESS
    public function updateColumnPosition(Request $request)
    {
        $request->validate([
            'columns' => 'required|array',
            'columns.*.id' => 'required|exists:board_columns,id',
            'columns.*.position' => 'required|integer',
            'workspace_id' => 'required|exists:workspaces,id'
        ]);

        try {
            $user = Auth::user();
            
            // Validasi akses user ke workspace
            $userWorkspace = UserWorkspace::where('user_id', $user->id)
                ->where('workspace_id', $request->workspace_id)
                ->first();

            if (!$userWorkspace) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke workspace ini'
                ], 403);
            }

            foreach ($request->columns as $columnData) {
                // ✅ FIXED: PASTIKAN KOLOM TERSEBUT MILIK WORKSPACE YANG SAMA
                $column = BoardColumn::where('id', $columnData['id'])
                    ->where('workspace_id', $request->workspace_id)
                    ->first();

                if ($column) {
                    $column->update(['position' => $columnData['position']]);
                } else {
                    \Log::warning("Column {$columnData['id']} not found in workspace {$request->workspace_id}");
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Posisi kolom berhasil diupdate'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating column positions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate posisi kolom: ' . $e->getMessage()
            ], 500);
        }
    }

    // Tampilkan halaman kanban
    public function showKanban(Workspace $workspace)
    {
        $user = Auth::user();
        $userWorkspace = UserWorkspace::where('user_id', $user->id)
            ->where('workspace_id', $workspace->id)
            ->first();

        if (!$userWorkspace) {
            abort(403, 'Anda tidak memiliki akses ke workspace ini');
        }

        // ✅ DEBUG: Log akses workspace
        \Log::info("User {$user->id} accessing kanban for workspace {$workspace->id} ({$workspace->name})");

        return view('kanban-tugas', compact('workspace'));
    }

    // ✅ NEW: Method untuk debug data
    public function debugBoardColumns($workspaceId)
    {
        $user = Auth::user();
        $activeCompanyId = session('active_company_id');
        
        $allColumnsInCompany = BoardColumn::whereHas('workspace', function($query) use ($activeCompanyId) {
            $query->where('company_id', $activeCompanyId);
        })->with('workspace')->get();
        
        $specificColumns = BoardColumn::where('workspace_id', $workspaceId)->get();
        
        return response()->json([
            'debug_info' => [
                'requested_workspace_id' => $workspaceId,
                'active_company_id' => $activeCompanyId,
                'all_columns_in_company_count' => $allColumnsInCompany->count(),
                'all_columns_in_company' => $allColumnsInCompany->map(function($col) {
                    return [
                        'id' => $col->id,
                        'name' => $col->name,
                        'workspace_id' => $col->workspace_id,
                        'workspace_name' => $col->workspace->name
                    ];
                }),
                'specific_workspace_columns_count' => $specificColumns->count(),
                'specific_workspace_columns' => $specificColumns
            ]
        ]);
    }
}