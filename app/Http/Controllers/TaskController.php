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
use App\Models\Label;
use App\Models\Color;
use App\Models\User;
use App\Models\Checklist;
use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TaskController extends Controller
{



    private function canAccessWorkspace($workspaceId)
    {
        $user = Auth::user();
        $activeCompanyId = session('active_company_id');

        // ✅ CEK APAKAH USER ADALAH SUPERADMIN/ADMIN/MANAGER DI COMPANY
        $userCompany = $user->userCompanies()
            ->where('company_id', $activeCompanyId)
            ->with('role')
            ->first();

        $userRole = $userCompany?->role?->name ?? 'Member';

        // ✅ JIKA SUPERADMIN/ADMIN/MANAGER, BOLEH AKSES SEMUA WORKSPACE DI COMPANY
        if (in_array($userRole, ['SuperAdmin', 'Administrator', 'Admin', 'Manager'])) {
            return true;
        }

        // ✅ JIKA BUKAN, CEK APAKAH USER ADALAH ANGGOTA WORKSPACE
        $userWorkspace = UserWorkspace::where('user_id', $user->id)
            ->where('workspace_id', $workspaceId)
            ->where('status_active', true)
            ->first();

        return !is_null($userWorkspace);
    }



    // ============================================================
    // === GET BOARD COLUMNS (HANYA UNTUK WORKSPACE YANG DIMINTA)
    // ============================================================
    public function getBoardColumns($workspaceId)
    {
        try {
            $user = Auth::user();

            // ✅ VALIDASI: Gunakan method helper untuk cek akses
            if (!$this->canAccessWorkspace($workspaceId)) {
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

            return response()->json([
                'success' => true,
                'columns' => $columns,
                'workspace_name' => $workspace->name,
                'workspace_id' => $workspace->id
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

            // ✅ VALIDASI: Gunakan method helper untuk cek akses
            if (!$this->canAccessWorkspace($request->workspace_id)) {
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

            return response()->json([
                'success' => true,
                'message' => 'Kolom berhasil ditambahkan',
                'column' => $column
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

        // ✅ VALIDASI: Gunakan method helper untuk cek akses
        if (!$this->canAccessWorkspace($workspace->id)) {
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

            // ✅ VALIDASI: Gunakan method helper untuk cek akses
            if (!$this->canAccessWorkspace($workspaceId)) {
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

            // ✅ VALIDASI: Gunakan method helper untuk cek akses workspace task
            if (!$this->canAccessWorkspace($task->workspace_id)) {
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

            // ✅ VALIDASI: Gunakan method helper untuk cek akses workspace task
            if (!$this->canAccessWorkspace($task->workspace_id)) {
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
    // ✅ UPDATE: Create task dengan assignments dan checklists
    // ✅ UPDATE: Create task dengan assignments, checklists, dan secret flag
    // ✅ UPDATE: Create task dengan attachments support
    public function storeWithAssignments(Request $request)
    {
        $request->validate([
            'workspace_id' => 'required|exists:workspaces,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id',
            'label_ids' => 'array',
            'label_ids.*' => 'exists:labels,id',
            'checklists' => 'array',
            'checklists.*.title' => 'required|string|max:255',
            'checklists.*.is_done' => 'boolean',
            'is_secret' => 'boolean',
            'attachment_ids' => 'array',
            'attachment_ids.*' => 'exists:attachments,id',
            'start_datetime' => 'nullable|date_format:Y-m-d H:i',
            'due_datetime' => 'nullable|date_format:Y-m-d H:i|after:start_datetime',
        ]);

        try {
            $user = Auth::user();

            // ✅ VALIDASI: Gunakan method helper untuk cek akses
            if (!$this->canAccessWorkspace($request->workspace_id)) {
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
                'board_column_id' => $request->board_column_id,
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

            // Attach labels jika ada
            if (!empty($request->label_ids)) {
                $task->labels()->attach($request->label_ids);
            }

            // Create checklists jika ada
            if (!empty($request->checklists)) {
                foreach ($request->checklists as $index => $checklistData) {
                    Checklist::create([
                        'id' => Str::uuid()->toString(),
                        'task_id' => $task->id,
                        'title' => $checklistData['title'],
                        'is_done' => $checklistData['is_done'] ?? false,
                        'position' => $index
                    ]);
                }
            }

            // Update attachments dengan task ID yang baru dibuat
            if (!empty($request->attachment_ids)) {
                Attachment::whereIn('id', $request->attachment_ids)
                    ->where('attachable_type', 'App\\Models\\Task')
                    ->whereNull('attachable_id')
                    ->update([
                        'attachable_id' => $task->id
                    ]);
            }

            DB::commit();

            // Load relations untuk response
            $task->load(['assignees', 'labels.color', 'checklists', 'attachments.uploader']);

            return response()->json([
                'success' => true,
                'message' => 'Tugas berhasil dibuat',
                'task' => $task,
                'is_secret' => $task->is_secret
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





    // untuk label dan warna pada tugas
    public function getLabels($workspaceId)
    {
        try {
            $user = Auth::user();

            // ✅ VALIDASI: Gunakan method helper untuk cek akses
            if (!$this->canAccessWorkspace($workspaceId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke workspace ini'
                ], 403);
            }

            $labels = Label::with('color')->get();

            return response()->json([
                'success' => true,
                'labels' => $labels
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting labels: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data label'
            ], 500);
        }
    }


    // ✅ NEW: Create new label
    // Di TaskController - perbaiki method createLabel
    public function createLabel(Request $request)
{
    Log::info('Create Label Request:', $request->all());

    $request->validate([
        'name' => 'required|string|max:255',
        'color_id' => 'required|exists:colors,id',
        'workspace_id' => 'required|exists:workspaces,id'
    ]);

    try {
        $user = Auth::user();
        $activeCompanyId = session('active_company_id');

        // ✅ GUNAKAN METHOD HELPER YANG SAMA SEPERTI METHOD LAIN
        if (!$this->canAccessWorkspace($request->workspace_id)) {
            Log::error('User tidak memiliki akses ke workspace', [
                'user_id' => $user->id,
                'workspace_id' => $request->workspace_id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke workspace ini'
            ], 403);
        }

        // Validasi workspace dalam company aktif
        $workspace = Workspace::find($request->workspace_id);
        if ($workspace->company_id !== $activeCompanyId) {
            return response()->json([
                'success' => false,
                'message' => 'Workspace tidak termasuk dalam perusahaan yang aktif'
            ], 403);
        }

        // Cek apakah label dengan nama yang sama sudah ada
        $existingLabel = Label::where('name', $request->name)->first();
        if ($existingLabel) {
            return response()->json([
                'success' => false,
                'message' => 'Label dengan nama ini sudah ada'
            ], 400);
        }

        $label = Label::create([
            'id' => Str::uuid()->toString(),
            'name' => $request->name,
            'color_id' => $request->color_id
        ]);

        // Load relation color untuk response
        $label->load('color');

        Log::info('Label berhasil dibuat:', [
            'label_id' => $label->id,
            'name' => $label->name,
            'color_id' => $label->color_id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Label berhasil dibuat',
            'label' => $label
        ]);
    } catch (\Exception $e) {
        Log::error('Error creating label: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Gagal membuat label: ' . $e->getMessage()
        ], 500);
    }
}

    // ✅ NEW: Get available colors
    public function getColors()
    {
        try {
            $colors = Color::all();

            return response()->json([
                'success' => true,
                'colors' => $colors
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting colors: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data warna'
            ], 500);
        }
    }

    // ✅ NEW: Manage task labels
    public function manageTaskLabels(Request $request, $taskId)
    {
        $request->validate([
            'label_ids' => 'required|array',
            'label_ids.*' => 'exists:labels,id'
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

            // Sync labels
            $task->labels()->sync($request->label_ids);

            // Get updated labels with colors
            $updatedLabels = $task->labels()->with('color')->get();

            return response()->json([
                'success' => true,
                'message' => 'Label tugas berhasil diupdate',
                'labels' => $updatedLabels
            ]);
        } catch (\Exception $e) {
            Log::error('Error managing task labels: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate label tugas: ' . $e->getMessage()
            ], 500);
        }
    }

    // ✅ NEW: Get task labels
    public function getTaskLabels($taskId)
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

            $labels = $task->labels()->with('color')->get();

            return response()->json([
                'success' => true,
                'labels' => $labels
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting task labels: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil label tugas'
            ], 500);
        }
    }


    // Tambahkan method ini ke TaskController
    public function getWorkspaceTasks($workspaceId)
    {
        try {
            $user = Auth::user();

            // ✅ VALIDASI: Gunakan method helper untuk cek akses
            if (!$this->canAccessWorkspace($workspaceId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke workspace ini'
                ], 403);
            }

            $tasks = Task::with(['assignees', 'labels.color', 'boardColumn'])
                ->where('workspace_id', $workspaceId)
                ->get();

            return response()->json([
                'success' => true,
                'tasks' => $tasks
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting workspace tasks: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data tugas'
            ], 500);
        }
    }



    // ✅ NEW: Get task checklists
    public function getTaskChecklists($taskId)
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

            $checklists = Checklist::where('task_id', $taskId)
                ->orderBy('position')
                ->get();

            return response()->json([
                'success' => true,
                'checklists' => $checklists
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting task checklists: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data checklist'
            ], 500);
        }
    }

    // ✅ NEW: Create checklist item
    public function createChecklist(Request $request)
    {
        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'title' => 'required|string|max:255'
        ]);

        try {
            $task = Task::findOrFail($request->task_id);
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

            $checklist = Checklist::create([
                'id' => Str::uuid()->toString(),
                'task_id' => $request->task_id,
                'title' => $request->title,
                'is_done' => false,
                'position' => Checklist::where('task_id', $request->task_id)->max('position') + 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Checklist berhasil ditambahkan',
                'checklist' => $checklist
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating checklist: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan checklist: ' . $e->getMessage()
            ], 500);
        }
    }

    // ✅ NEW: Update checklist item
    public function updateChecklist(Request $request, $checklistId)
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'is_done' => 'sometimes|boolean'
        ]);

        try {
            $checklist = Checklist::findOrFail($checklistId);
            $task = Task::findOrFail($checklist->task_id);
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

            $checklist->update($request->only(['title', 'is_done']));

            return response()->json([
                'success' => true,
                'message' => 'Checklist berhasil diupdate',
                'checklist' => $checklist
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating checklist: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate checklist: ' . $e->getMessage()
            ], 500);
        }
    }

    // ✅ NEW: Delete checklist item
    public function deleteChecklist($checklistId)
    {
        try {
            $checklist = Checklist::findOrFail($checklistId);
            $task = Task::findOrFail($checklist->task_id);
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

            $checklist->delete();

            return response()->json([
                'success' => true,
                'message' => 'Checklist berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting checklist: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus checklist: ' . $e->getMessage()
            ], 500);
        }
    }

    // ✅ NEW: Update checklist positions
    public function updateChecklistPositions(Request $request)
    {
        $request->validate([
            'checklists' => 'required|array',
            'checklists.*.id' => 'required|exists:checklists,id',
            'checklists.*.position' => 'required|integer'
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->checklists as $checklistData) {
                $checklist = Checklist::find($checklistData['id']);
                if ($checklist) {
                    $task = Task::findOrFail($checklist->task_id);
                    $user = Auth::user();

                    // Validasi akses user
                    $userWorkspace = UserWorkspace::where('user_id', $user->id)
                        ->where('workspace_id', $task->workspace_id)
                        ->first();

                    if ($userWorkspace) {
                        $checklist->update(['position' => $checklistData['position']]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Posisi checklist berhasil diupdate'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating checklist positions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate posisi checklist: ' . $e->getMessage()
            ], 500);
        }
    }


    // ✅ NEW: Get tasks dengan filter berdasarkan hak akses (secret/non-secret)
    public function getWorkspaceTasksWithAccess($workspaceId)
    {
        try {
            $user = Auth::user();

            // ✅ VALIDASI: Gunakan method helper untuk cek akses
            if (!$this->canAccessWorkspace($workspaceId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke workspace ini'
                ], 403);
            }

            // Ambil semua tugas di workspace
            $query = Task::with(['assignees', 'labels.color', 'boardColumn', 'taskAssignments'])
                ->where('workspace_id', $workspaceId);

            // Jika user bukan SuperAdmin/Administrator, filter tugas rahasia
            $userCompany = $user->userCompanies()
                ->where('company_id', session('active_company_id'))
                ->with('role')
                ->first();

            $userRole = $userCompany?->role?->name ?? 'Member';

            if (!in_array($userRole, ['SuperAdmin', 'Administrator', 'Admin'])) {
                // Tampilkan:
                // 1. Semua tugas non-rahasia (is_secret = false)
                // 2. Tugas rahasia hanya jika user adalah assigned member
                $query->where(function ($q) use ($user) {
                    $q->where('is_secret', false)
                        ->orWhereHas('taskAssignments', function ($assignmentQuery) use ($user) {
                            $assignmentQuery->where('user_id', $user->id);
                        });
                });
            }

            $tasks = $query->get();

            return response()->json([
                'success' => true,
                'tasks' => $tasks,
                'user_role' => $userRole
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting workspace tasks with access: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data tugas'
            ], 500);
        }
    }




    // ============================================================
    // === ATTACHMENT METHODS
    // ============================================================

    /**
     * Upload attachment untuk task
     */
    /**
     * Upload attachment untuk task
     */
    public function uploadAttachment(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
            'attachable_type' => 'required|string',
            // HAPUS: 'attachable_id' => 'required' - karena belum ada ID tugas
        ]);

        try {
            $user = Auth::user();
            $file = $request->file('file');

            // Validasi tipe file
            $allowedMimeTypes = [
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/gif',
                'image/webp',
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'text/plain',
                'application/zip',
                'application/x-rar-compressed'
            ];

            if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipe file tidak didukung.'
                ], 400);
            }

            // Generate unique filename
            $fileName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

            // Simpan file
            $path = $file->storeAs('attachments', $fileName, 'public');

            // ✅ BUAT ATTACHMENT TANPA attachable_id (akan diupdate nanti)
            $attachment = Attachment::create([
                'id' => Str::uuid()->toString(),
                'attachable_type' => $request->attachable_type,
                'attachable_id' => null, // Biarkan null untuk sementara
                'file_url' => $path,
                'uploaded_by' => $user->id,
                'uploaded_at' => now()
            ]);

            $attachment->load('uploader');

            Log::info('File berhasil diupload (sementara):', [
                'attachment_id' => $attachment->id,
                'file_url' => $attachment->file_url,
                'attachable_id' => $attachment->attachable_id // Masih null
            ]);

            return response()->json([
                'success' => true,
                'attachment' => $attachment,
                'message' => 'File berhasil diupload'
            ]);
        } catch (\Exception $e) {
            Log::error('Error uploading attachment: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal upload file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attachments untuk task
     */
    public function getTaskAttachments($taskId)
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

            $attachments = $task->attachments()
                ->with('uploader')
                ->orderBy('uploaded_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'attachments' => $attachments
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting task attachments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil lampiran'
            ], 500);
        }
    }

    /**
     * Delete attachment
     */
    public function deleteAttachment($attachmentId)
    {
        try {
            $user = Auth::user();
            $attachment = Attachment::findOrFail($attachmentId);

            // Validasi: hanya uploader atau admin yang bisa hapus
            if ($attachment->uploaded_by !== $user->id) {
                // Cek jika user adalah admin di workspace
                $attachable = $attachment->attachable;
                if ($attachable instanceof Task) {
                    $userWorkspace = UserWorkspace::where('user_id', $user->id)
                        ->where('workspace_id', $attachable->workspace_id)
                        ->with('role')
                        ->first();

                    $userRole = $userWorkspace?->role?->name ?? 'Member';
                    if (!in_array($userRole, ['SuperAdmin', 'Administrator', 'Admin'])) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Anda tidak memiliki izin untuk menghapus lampiran ini'
                        ], 403);
                    }
                }
            }

            // Hapus file dari storage
            if (Storage::disk('public')->exists($attachment->file_url)) {
                Storage::disk('public')->delete($attachment->file_url);
            }

            $attachment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Lampiran berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting attachment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus lampiran'
            ], 500);
        }
    }

    /**
     * Download attachment
     */
    public function downloadAttachment($attachmentId)
    {
        try {
            $attachment = Attachment::findOrFail($attachmentId);
            $user = Auth::user();

            // Validasi akses user
            $attachable = $attachment->attachable;
            if ($attachable instanceof Task) {
                $userWorkspace = UserWorkspace::where('user_id', $user->id)
                    ->where('workspace_id', $attachable->workspace_id)
                    ->first();

                if (!$userWorkspace) {
                    abort(403, 'Anda tidak memiliki akses ke file ini');
                }
            }

            if (!Storage::disk('public')->exists($attachment->file_url)) {
                abort(404, 'File tidak ditemukan');
            }

            return Storage::disk('public')->download($attachment->file_url, $attachment->file_name);
        } catch (\Exception $e) {
            Log::error('Error downloading attachment: ' . $e->getMessage());
            abort(404, 'File tidak ditemukan');
        }
    }
}
