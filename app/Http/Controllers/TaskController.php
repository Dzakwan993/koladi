<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\DB;
use App\Models\BoardColumn;
use App\Models\Workspace;
use App\Models\UserWorkspace;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\User;
use Illuminate\Support\Str;

class TaskController extends Controller
{
    // ============================================================
    // === GET BOARD COLUMNS (HANYA UNTUK WORKSPACE YANG DIMINTA)
    // ============================================================
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

            // Ambil kolom dari workspace ini saja
            $columns = BoardColumn::where('workspace_id', $workspaceId)
                ->orderBy('position')
                ->get();

            // Debug log
            Log::info("Board columns for workspace {$workspaceId}: {$columns->count()} columns found");
            foreach ($columns as $col) {
                Log::info(" - Column: {$col->name} (ID: {$col->id}, Workspace: {$col->workspace_id})");
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
            Log::error('Error getting board columns: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kolom: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================================
    // === CREATE BOARD COLUMN (HANYA UNTUK WORKSPACE YANG DIAKSES)
    // ============================================================
    public function createBoardColumn(Request $request)
    {
        $request->validate([
            'workspace_id' => 'required|exists:workspaces,id',
            'name' => 'required|string|max:255'
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

            // Validasi workspace dalam company aktif
            $workspace = Workspace::find($request->workspace_id);
            $activeCompanyId = session('active_company_id');

            if ($workspace->company_id !== $activeCompanyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Workspace tidak termasuk dalam perusahaan yang aktif'
                ], 403);
            }

            // Hitung posisi terakhir di workspace ini
            $lastPosition = BoardColumn::where('workspace_id', $request->workspace_id)->max('position');

            $column = BoardColumn::create([
                'id' => Str::uuid()->toString(),
                'workspace_id' => $request->workspace_id,
                'name' => $request->name,
                'position' => $lastPosition ? $lastPosition + 1 : 1,
                'created_by' => $user->id
            ]);

            Log::info("New column created: {$column->name} for workspace {$request->workspace_id}");

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
            Log::error('Error creating board column: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan kolom: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================================
    // === DELETE BOARD COLUMN (VALIDASI WORKSPACE ACCESS)
    // ============================================================
    public function deleteBoardColumn($columnId)
    {
        try {
            $user = Auth::user();
            $column = BoardColumn::with('workspace')->findOrFail($columnId);

            // Validasi akses user
            $userWorkspace = UserWorkspace::where('user_id', $user->id)
                ->where('workspace_id', $column->workspace_id)
                ->first();

            if (!$userWorkspace) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke workspace ini'
                ], 403);
            }

            // Cegah penghapusan kolom default
            $defaultColumns = ['To Do List', 'Dikerjakan', 'Selesai', 'Batal'];
            if (in_array($column->name, $defaultColumns)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kolom default tidak dapat dihapus'
                ], 400);
            }

            $column->delete();
            Log::info("Column deleted: {$column->name} from workspace {$column->workspace_id}");

            return response()->json([
                'success' => true,
                'message' => 'Kolom berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting board column: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kolom: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================================
    // === UPDATE COLUMN POSITION (VALIDASI WORKSPACE ACCESS)
    // ============================================================
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

            // Validasi akses user
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
                $column = BoardColumn::where('id', $columnData['id'])
                    ->where('workspace_id', $request->workspace_id)
                    ->first();

                if ($column) {
                    $column->update(['position' => $columnData['position']]);
                } else {
                    Log::warning("Column {$columnData['id']} not found in workspace {$request->workspace_id}");
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Posisi kolom berhasil diupdate'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating column positions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate posisi kolom: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================================
    // === SHOW KANBAN PAGE
    // ============================================================
    public function showKanban(Workspace $workspace)
    {
        $user = Auth::user();
        $userWorkspace = UserWorkspace::where('user_id', $user->id)
            ->where('workspace_id', $workspace->id)
            ->first();

        if (!$userWorkspace) {
            abort(403, 'Anda tidak memiliki akses ke workspace ini');
        }

        Log::info("User {$user->id} accessing kanban for workspace {$workspace->id} ({$workspace->name})");

        return view('kanban-tugas', compact('workspace'));
    }

    // ============================================================
    // === DEBUG BOARD COLUMN DATA
    // ============================================================
    public function debugBoardColumns($workspaceId)
    {
        $user = Auth::user();
        $activeCompanyId = session('active_company_id');

        $allColumnsInCompany = BoardColumn::whereHas('workspace', function ($query) use ($activeCompanyId) {
            $query->where('company_id', $activeCompanyId);
        })->with('workspace')->get();

        $specificColumns = BoardColumn::where('workspace_id', $workspaceId)->get();

        return response()->json([
            'debug_info' => [
                'requested_workspace_id' => $workspaceId,
                'active_company_id' => $activeCompanyId,
                'all_columns_in_company_count' => $allColumnsInCompany->count(),
                'all_columns_in_company' => $allColumnsInCompany->map(function ($col) {
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






    // ✅ NEW: Get anggota workspace untuk tugas
    public function getWorkspaceMembers($workspaceId)
    {
        try {
            $user = Auth::user();
            
            // Validasi akses user ke workspace
            $userWorkspace = UserWorkspace::where('user_id', $user->id)
                ->where('workspace_id', $workspaceId)
                ->first();

            if (!$userWorkspace) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke workspace ini'
                ], 403);
            }

            // Get semua anggota aktif di workspace
            $members = UserWorkspace::with('user')
                ->where('workspace_id', $workspaceId)
                ->where('status_active', true)
                ->get()
                ->map(function ($userWorkspace) {
                    return [
                        'id' => $userWorkspace->user->id,
                        'name' => $userWorkspace->user->full_name,
                        'email' => $userWorkspace->user->email,
                        'avatar' => 'https://i.pravatar.cc/32?img=' . (rand(1, 70)),
                        'role' => $userWorkspace->role->name ?? 'Member'
                    ];
                });

            return response()->json([
                'success' => true,
                'members' => $members
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting workspace members: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data anggota'
            ], 500);
        }
    }

    // ✅ NEW: Get anggota yang sudah ditugaskan ke task
    public function getTaskAssignments($taskId)
    {
        try {
            $task = Task::findOrFail($taskId);
            $user = Auth::user();

            // Validasi akses user ke workspace task
            $userWorkspace = UserWorkspace::where('user_id', $user->id)
                ->where('workspace_id', $task->workspace_id)
                ->first();

            if (!$userWorkspace) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke task ini'
                ], 403);
            }

            // Get semua user yang ditugaskan ke task
            $assignedUsers = TaskAssignment::with('user')
                ->where('task_id', $taskId)
                ->get()
                ->map(function ($assignment) {
                    return [
                        'id' => $assignment->user->id,
                        'name' => $assignment->user->full_name,
                        'email' => $assignment->user->email,
                        'avatar' => 'https://i.pravatar.cc/32?img=' . (rand(1, 70))
                    ];
                });

            return response()->json([
                'success' => true,
                'assigned_members' => $assignedUsers
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting task assignments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data anggota task'
            ], 500);
        }
    }

    // ✅ NEW: Manage anggota tugas (assign/unassign)
public function manageTaskAssignments(Request $request, $taskId)
{
    $request->validate([
        'user_ids' => 'required|array',
        'user_ids.*' => 'exists:users,id'
    ]);

    try {
        $task = Task::findOrFail($taskId);
        $user = Auth::user();

        // Validasi akses user ke workspace task
        $userWorkspace = UserWorkspace::where('user_id', $user->id)
            ->where('workspace_id', $task->workspace_id)
            ->first();

        if (!$userWorkspace) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke task ini'
            ], 403);
        }

        // Validasi bahwa semua user_ids adalah anggota workspace
        $workspaceMemberIds = UserWorkspace::where('workspace_id', $task->workspace_id)
            ->where('status_active', true)
            ->pluck('user_id')
            ->toArray();

        $invalidUsers = array_diff($request->user_ids, $workspaceMemberIds);
        if (!empty($invalidUsers)) {
            return response()->json([
                'success' => false,
                'message' => 'Beberapa user bukan anggota workspace ini'
            ], 400);
        }

        // Hapus assignment yang tidak dipilih
        TaskAssignment::where('task_id', $taskId)
            ->whereNotIn('user_id', $request->user_ids)
            ->delete();

        // Tambah assignment baru
        foreach ($request->user_ids as $userId) {
            TaskAssignment::updateOrCreate(
                [
                    'task_id' => $taskId,
                    'user_id' => $userId
                ],
                [
                    'assigned_at' => now()
                ]
            );
        }

        // ✅ PASTIKAN: Get updated assigned members dengan query yang sama
        $assignedMembers = TaskAssignment::with('user')
            ->where('task_id', $taskId)
            ->get()
            ->map(function ($assignment) {
                return [
                    'id' => $assignment->user->id,
                    'name' => $assignment->user->full_name,
                    'email' => $assignment->user->email,
                    'avatar' => 'https://i.pravatar.cc/32?img=' . (rand(1, 70))
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Anggota tugas berhasil diupdate',
            'assigned_members' => $assignedMembers,
            // ✅ TAMBAHKAN: Juga kembalikan user_ids untuk konsistensi
            'user_ids' => $request->user_ids
        ]);

    } catch (\Exception $e) {
        Log::error('Error managing task assignments: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Gagal mengupdate anggota tugas: ' . $e->getMessage()
        ], 500);
    }
}

    // ✅ NEW: Create task dengan assignments
    public function storeWithAssignments(Request $request)
    {
        $request->validate([
            'workspace_id' => 'required|exists:workspaces,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id'
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

            DB::beginTransaction();

            // Buat task
            $task = Task::create([
                'id' => Str::uuid()->toString(),
                'workspace_id' => $request->workspace_id,
                'created_by' => $user->id,
                'title' => $request->title,
                'description' => $request->description,
                'board_column_id' => $request->board_column_id, // jika ada
                'status' => 'todo',
                'priority' => $request->priority ?? 'medium',
                'is_secret' => $request->is_secret ?? false,
                'start_datetime' => $request->start_datetime,
                'due_datetime' => $request->due_datetime,
                'phase' => $request->phase
            ]);

            // Assign anggota jika ada
            if (!empty($request->user_ids)) {
                foreach ($request->user_ids as $userId) {
                    TaskAssignment::create([
                        'id' => Str::uuid()->toString(),
                        'task_id' => $task->id,
                        'user_id' => $userId,
                        'assigned_at' => now()
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tugas berhasil dibuat',
                'task' => $task
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating task with assignments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat tugas: ' . $e->getMessage()
            ], 500);
        }
    }
}

