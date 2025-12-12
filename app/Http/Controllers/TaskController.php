<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\BoardColumn;
use App\Models\Workspace;
use App\Models\UserWorkspace;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\Label;
use App\Models\Color;
use App\Models\Comment;
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

    // Di App\Http\Controllers\TaskController

    /**
     * Update task column ketika drag & drop
     */
    // Di App\Http\Controllers\TaskController

    /**
     * Update task column ketika drag & drop
     */
    public function updateTaskColumn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'task_id' => 'required|exists:tasks,id',
            'board_column_id' => 'required|exists:board_columns,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $task = Task::findOrFail($request->task_id);
            $user = Auth::user();

            // Validasi akses
            if (!$this->canAccessWorkspace($task->workspace_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke task ini'
                ], 403);
            }

            // Validasi bahwa board column termasuk dalam workspace yang sama
            $boardColumn = BoardColumn::where('id', $request->board_column_id)
                ->where('workspace_id', $task->workspace_id)
                ->first();

            if (!$boardColumn) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kolom board tidak valid untuk workspace ini'
                ], 422);
            }

            DB::beginTransaction();

            // Pindahkan task ke kolom baru dan sync status
            $task->moveToColumn($request->board_column_id);

            DB::commit();

            // Reload task dengan relasi terbaru
            $task->load(['boardColumn', 'assignments.user', 'labels.color']);

            return response()->json([
                'success' => true,
                'message' => 'Tugas berhasil dipindahkan',
                'task' => $task,
                'new_status' => $task->status,
                'new_column_name' => $task->boardColumn->name
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating task column: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memindahkan tugas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create task dengan sync status otomatis
     */
    public function storeWithAssignments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'workspace_id' => 'required|exists:workspaces,id',
            'board_column_id' => 'required|exists:board_columns,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'phase' => 'required|string|max:255',
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
            'start_datetime' => 'nullable|date_format:Y-m-d H:i:s',
            'due_datetime' => 'nullable|date_format:Y-m-d H:i:s|after:start_datetime'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();

            if (!$this->canAccessWorkspace($request->workspace_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke workspace ini'
                ], 403);
            }

            // Validasi board column
            $boardColumn = BoardColumn::where('id', $request->board_column_id)
                ->where('workspace_id', $request->workspace_id)
                ->first();

            if (!$boardColumn) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kolom board tidak valid untuk workspace ini'
                ], 422);
            }

            DB::beginTransaction();

            // Tentukan status berdasarkan kolom (mengikuti nama kolom untuk custom)
            $status = $this->mapColumnToStatus($boardColumn->name);

            // Buat task
            $taskData = [
                'id' => Str::uuid()->toString(),
                'workspace_id' => $request->workspace_id,
                'board_column_id' => $request->board_column_id,
                'created_by' => $user->id,
                'title' => $request->title,
                'description' => $request->description,
                'phase' => $request->phase,
                'status' => $status,
                'priority' => $request->priority ?? 'medium',
                'is_secret' => $request->is_secret ?? false
            ];

            // Tambahkan datetime jika ada
            if ($request->start_datetime) {
                $taskData['start_datetime'] = $request->start_datetime;
            }
            if ($request->due_datetime) {
                $taskData['due_datetime'] = $request->due_datetime;
            }

            $task = Task::create($taskData);

            // Assign anggota
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

            if (!empty($request->label_ids)) {
                $task->labels()->attach($request->label_ids);
            }

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

            if (!empty($request->attachment_ids)) {
    // ✅ PERBAIKAN: Update attachments tanpa mass assignment
    foreach ($request->attachment_ids as $attachmentId) {
        $attachment = Attachment::find($attachmentId);
        if ($attachment) {
            $attachment->attachable_id = $task->id;
            $attachment->save();
        }
    }
}

            DB::commit();

            // ✅ PERBAIKI: Load data dengan format yang diharapkan frontend
            $task->load([
                'assignments.user', // Tetap load assignments
                'labels.color',
                'checklists',
                'attachments',
                'boardColumn',
                'creator' // Load creator jika ada relasi
            ]);

            // ✅ PERBAIKI: Format response untuk frontend
            $formattedTask = [
                'id' => $task->id,
                'title' => $task->title,
                'phase' => $task->phase,
                'status' => $task->status,
                'board_column_id' => $task->board_column_id,
                'description' => $task->description,
                'is_secret' => $task->is_secret,
                'priority' => $task->priority,
                'start_datetime' => $task->start_datetime,
                'due_datetime' => $task->due_datetime,
                'progress_percentage' => $task->progress_percentage ?? 0,
                'is_overdue' => $task->is_overdue ?? false,
                'created_at' => $task->created_at,
                'updated_at' => $task->updated_at,

                // ✅ FORMAT assignees yang diharapkan frontend
                'assignees' => $task->assignments->map(function ($assignment) {
                    return [
                        'id' => $assignment->user->id,
                        'name' => $assignment->user->full_name ?? $assignment->user->name,
                        'email' => $assignment->user->email,
                        'avatar' => $assignment->user->avatar ?? 'https://i.pravatar.cc/40?img=0'
                    ];
                }),

                // ✅ FORMAT labels yang diharapkan frontend
                'labels' => $task->labels->map(function ($label) {
                    return [
                        'id' => $label->id,
                        'name' => $label->name,
                        'color' => $label->color->rgb // Pastikan ada field rgb
                    ];
                }),

                // ✅ FORMAT checklists yang diharapkan frontend
                'checklists' => $task->checklists->map(function ($checklist) {
                    return [
                        'id' => $checklist->id,
                        'title' => $checklist->title,
                        'is_done' => $checklist->is_done,
                        'position' => $checklist->position
                    ];
                }),

                // ✅ FORMAT attachments yang diharapkan frontend
                'attachments' => $task->attachments->map(function ($attachment) {
                    return [
                        'id' => $attachment->id,
                        'name' => $attachment->original_name,
                        'size' => $attachment->file_size,
                        'url' => Storage::url($attachment->file_path),
                        'type' => $this->getFileTypeFromMime($attachment->mime_type)
                    ];
                }),

                'board_column' => [
                    'id' => $task->boardColumn->id,
                    'name' => $task->boardColumn->name,
                    'color' => $task->boardColumn->color ?? '#3b82f6'
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'Tugas berhasil dibuat',
                'task' => $formattedTask, // ✅ Gunakan formatted task
                'new_column_name' => $boardColumn->name
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating task: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat tugas: ' . $e->getMessage()
            ], 500);
        }
    }

    // ✅ TAMBAHKAN: Helper method untuk menentukan tipe file
    private function getFileTypeFromMime($mimeType)
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }
        if ($mimeType === 'application/pdf') {
            return 'pdf';
        }
        if (in_array($mimeType, ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])) {
            return 'doc';
        }
        if (in_array($mimeType, ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])) {
            return 'xls';
        }
        return 'other';
    }

    /**
     * Mapping nama kolom ke status
     */
    private function mapColumnToStatus($columnName)
    {
        $mapping = [
            'To Do List' => 'todo',
            'Dikerjakan' => 'inprogress',
            'Selesai' => 'done',
            'Batal' => 'cancel'
        ];

        // Untuk kolom default, gunakan mapping
        if (array_key_exists($columnName, $mapping)) {
            return $mapping[$columnName];
        }

        // Untuk kolom custom, gunakan nama kolom sebagai status
        // Konversi ke lowercase dan replace spasi dengan underscore
        return strtolower(str_replace(' ', '_', $columnName));
    }

    // Helper method untuk mendapatkan default board column
    private function getDefaultBoardColumnId($workspaceId)
    {
        $defaultColumn = BoardColumn::where('workspace_id', $workspaceId)
            ->where('name', 'like', '%To Do%')
            ->first();

        return $defaultColumn ? $defaultColumn->id : null;
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

            // ✅ PERBAIKI: Gunakan sync yang aman
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
    // Di TaskController.php - PERBAIKI method updateChecklist
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

        // ✅ PERBAIKI: Gunakan method helper untuk cek akses
        if (!$this->canAccessWorkspace($task->workspace_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke task ini'
            ], 403);
        }

        // ✅ UPDATE CHECKLIST
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
    // Di TaskController.php - PERBAIKI method deleteChecklist
public function deleteChecklist($checklistId)
{
    try {
        $checklist = Checklist::findOrFail($checklistId);
        $task = Task::findOrFail($checklist->task_id);
        $user = Auth::user();

        // ✅ PERBAIKI: Gunakan method helper untuk cek akses
        if (!$this->canAccessWorkspace($task->workspace_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke task ini'
            ], 403);
        }

        // ✅ DELETE CHECKLIST
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
        $originalName = $file->getClientOriginalName();
        $fileName = time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

        // Simpan file
        $path = $file->storeAs('attachments', $fileName, 'public');

        // ✅ PERBAIKAN: Buat attachment TANPA field file_type (karena ada accessor di model)
        $attachment = new Attachment();
        $attachment->id = Str::uuid()->toString();
        $attachment->attachable_type = $request->attachable_type;
        $attachment->attachable_id = $request->attachable_id ?? null;
        $attachment->file_url = $path;
        $attachment->file_name = $originalName;
        $attachment->file_size = $file->getSize();
        $attachment->uploaded_by = $user->id;
        $attachment->uploaded_at = now();

        // ✅ Save tanpa mass assignment untuk avoid error
        $attachment->save();

        Log::info('File uploaded successfully:', [
            'id' => $attachment->id,
            'file_name' => $originalName,
            'file_size' => $file->getSize(),
            'path' => $path
        ]);

        // ✅ Return data lengkap untuk frontend
        return response()->json([
            'success' => true,
            'attachment' => [
                'id' => $attachment->id,
                'file_url' => $path,
                'file_name' => $originalName,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'uploaded_at' => $attachment->uploaded_at->toISOString(),
                'uploaded_by' => $attachment->uploaded_by
            ],
            'message' => 'File berhasil diupload'
        ]);
    } catch (\Exception $e) {
        Log::error('Error uploading attachment: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());

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


    // ✅ NEW: Get tasks untuk kanban board dengan relasi lengkap
    public function getKanbanTasks($workspaceId)
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

            // Ambil semua tugas di workspace dengan relasi lengkap
            $query = Task::with([
                'assignments.user',
                'labels.color',
                'checklists',
                'boardColumn',
                'creator'
            ])
                ->where('workspace_id', $workspaceId);

            // Filter hak akses untuk tugas rahasia
            $userCompany = $user->userCompanies()
                ->where('company_id', session('active_company_id'))
                ->with('role')
                ->first();

            $userRole = $userCompany?->role?->name ?? 'Member';

            if (!in_array($userRole, ['SuperAdmin', 'Administrator', 'Admin'])) {
                $query->where(function ($q) use ($user) {
                    $q->where('is_secret', false)
                        ->orWhere('created_by', $user->id)
                        ->orWhereHas('assignments', function ($assignmentQuery) use ($user) {
                            $assignmentQuery->where('user_id', $user->id);
                        });
                });
            }

            $tasks = $query->get()->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'status' => $task->status,
                    'board_column_id' => $task->board_column_id,
                    'priority' => $task->priority,
                    'is_secret' => $task->is_secret,
                    'phase' => $task->phase,
                    'start_datetime' => $task->start_datetime?->toISOString(),
                    'due_datetime' => $task->due_datetime?->toISOString(),
                    'created_at' => $task->created_at->toISOString(),
                    'updated_at' => $task->updated_at->toISOString(),
                    'assignees' => $task->assignments->map(function ($assignment) {
                        return [
                            'id' => $assignment->user->id,
                            'name' => $assignment->user->full_name,
                            'email' => $assignment->user->email,
                            'avatar' => $assignment->user->avatar ?: 'https://i.pravatar.cc/32?img=' . rand(1, 70)
                        ];
                    }),
                    'labels' => $task->labels->map(function ($label) {
                        return [
                            'id' => $label->id,
                            'name' => $label->name,
                            'color' => $label->color->rgb
                        ];
                    }),
                    'checklists' => $task->checklists->map(function ($checklist) {
                        return [
                            'id' => $checklist->id,
                            'title' => $checklist->title,
                            'is_done' => $checklist->is_done,
                            'position' => $checklist->position
                        ];
                    }),
                    'progress_percentage' => $task->getProgressPercentage(),
                    'is_overdue' => $task->isOverdue()
                ];
            });

            return response()->json([
                'success' => true,
                'tasks' => $tasks,
                'user_role' => $userRole
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting kanban tasks: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data tugas untuk kanban'
            ], 500);
        }
    }



    // ✅ NEW: Get task detail untuk modal
    // ✅ NEW: Get task detail untuk modal dengan semua relasi lengkap
    // ✅ PERBAIKI: Get task detail untuk modal
    public function getTaskDetail($taskId)
    {
        try {
            // Validasi UUID
            if (!Str::isUuid($taskId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID tugas tidak valid'
                ], 400);
            }

            // Load task utama
            $task = Task::with([
                'assignments.user',
                'labels.color',
                'checklists' => function ($query) {
                    $query->orderBy('position');
                },
                'attachments',
                'boardColumn',
                'creator'
            ])->find($taskId);

            if (!$task) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tugas tidak ditemukan'
                ], 404);
            }

            // Validasi akses workspace
            if (!$this->canAccessWorkspace($task->workspace_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke task ini'
                ], 403);
            }

            // 🔥 Load comments + user + replies + user
            $task->load([
                'comments' => function ($query) {
                    $query->whereNull('parent_comment_id')
                        ->orderBy('created_at', 'desc');
                },
                'comments.user',
                'comments.replies.user'
            ]);

            // Format response sesuai frontend
            $taskData = [
                'id' => $task->id,
                'title' => $task->title,
                'phase' => $task->phase,
                'description' => $task->description,
                'is_secret' => $task->is_secret,
                'status' => $task->status,
                'priority' => $task->priority,
                'start_datetime' => $task->start_datetime ? $task->start_datetime->toIso8601String() : null,
                'due_datetime' => $task->due_datetime ? $task->due_datetime->toIso8601String() : null,
                'created_at' => $task->created_at?->toIso8601String(),
                'updated_at' => $task->updated_at?->toIso8601String(),

                'board_column' => $task->boardColumn ? [
                    'id' => $task->boardColumn->id,
                    'name' => $task->boardColumn->name,
                ] : null,

                'creator' => $task->creator ? [
                    'id' => $task->creator->id,
                    'name' => $task->creator->full_name,
                    'email' => $task->creator->email
                ] : null,

                'assigned_members' => $task->assignments->map(function ($assignment) {
                    return [
                        'id' => $assignment->user->id,
                        'name' => $assignment->user->full_name,
                        'email' => $assignment->user->email,
                        'avatar' => $assignment->user->avatar ?? 'https://i.pravatar.cc/32?img=' . rand(1, 70)
                    ];
                })->toArray(),

                'labels' => $task->labels->map(function ($label) {
                    return [
                        'id' => $label->id,
                        'name' => $label->name,
                        'color' => $label->color->rgb
                    ];
                })->toArray(),

                'checklists' => $task->checklists->map(function ($checklist) {
                    return [
                        'id' => $checklist->id,
                        'title' => $checklist->title,
                        'is_done' => (bool)$checklist->is_done,
                        'position' => $checklist->position
                    ];
                })->toArray(),

                // 🆕 Attachments mapping
                'attachments' => $task->attachments->map(function ($attachment) {
    // ✅ PERBAIKAN: Ambil nama file dari attribute atau file_url
    $fileName = $attachment->file_name ?? basename($attachment->file_url);

    return [
        'id' => $attachment->id,
        'name' => $fileName,
        'url' => (Storage::disk('public')->exists($attachment->file_url)
            ? Storage::disk('public')->url($attachment->file_url)
            : $attachment->file_url),
        'type' => pathinfo($fileName, PATHINFO_EXTENSION),
        'size' => $attachment->file_size ?? 0,
        'uploaded_by' => $attachment->uploader ? [
            'name' => $attachment->uploader->full_name
        ] : null,
        'uploaded_at' => $attachment->uploaded_at?->toIso8601String()
    ];
})->toArray(),

                'progress_percentage' => $this->calculateTaskProgress($task),
                'is_overdue' => $task->due_datetime && $task->due_datetime->lt(now()) && !in_array($task->status, ['done', 'cancel']),

                // ============================
                // 🔥 FORMAT KOMENTAR (AMAN)
                // ============================
                'comments' => $task->comments->map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'content' => $comment->content,
                        'author' => [
                            'id' => $comment->user->id ?? null,
                            'name' => $comment->user->full_name ?? $comment->user->name ?? 'Unknown',
                            'avatar' => $comment->user->avatar ?? 'https://i.pravatar.cc/40?img=0'
                        ],
                        'createdAt' => $comment->created_at->toIso8601String(),

                        // 🔥 Balasan komentar
                        'replies' => $comment->replies->map(function ($reply) {
                            return [
                                'id' => $reply->id,
                                'content' => $reply->content,
                                'author' => [
                                    'id' => $reply->user->id ?? null,
                                    'name' => $reply->user->full_name ?? $reply->user->name ?? 'Unknown',
                                    'avatar' => $reply->user->avatar ?? 'https://i.pravatar.cc/40?img=0'
                                ],
                                'createdAt' => $reply->created_at->toIso8601String(),
                            ];
                        })->toArray()
                    ];
                })->toArray()
            ];

            return response()->json([
                'success' => true,
                'task' => $taskData
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting task detail: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail tugas'
            ], 500);
        }
    }





    // Helper method untuk menghitung progress
    private function calculateTaskProgress($task)
    {
        if ($task->checklists->count() === 0) {
            return 0;
        }

        $completed = $task->checklists->where('is_done', true)->count();
        return round(($completed / $task->checklists->count()) * 100);
    }

    // Helper method untuk menentukan tipe file
    private function getFileType($fileUrl)
    {
        $extension = pathinfo($fileUrl, PATHINFO_EXTENSION);

        $imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $documentTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'];

        if (in_array(strtolower($extension), $imageTypes)) {
            return 'image';
        } elseif (in_array(strtolower($extension), $documentTypes)) {
            return 'document';
        } else {
            return 'other';
        }
    }



    // ✅ NEW: Update task detail dengan semua field
   public function updateTaskDetail(Request $request, $taskId)
{
    try {
        $task = Task::findOrFail($taskId);
        $user = Auth::user();

        // Validasi akses
        if (!$this->canAccessWorkspace($task->workspace_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke task ini'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'phase' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'is_secret' => 'boolean',
            'start_datetime' => 'nullable|date',
            'due_datetime' => 'nullable|date|after:start_datetime',
            'board_column_id' => 'sometimes|exists:board_columns,id',
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id',
            'label_ids' => 'array',
            'label_ids.*' => 'exists:labels,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        // Update task data
        $taskData = $request->only([
            'title',
            'phase',
            'description',
            'is_secret'
        ]);

        // Handle datetime fields
        if ($request->has('start_datetime')) {
            $taskData['start_datetime'] = $request->start_datetime;
        }

        if ($request->has('due_datetime')) {
            $taskData['due_datetime'] = $request->due_datetime;
        }

        // Update board column jika ada
        if ($request->has('board_column_id')) {
            $taskData['board_column_id'] = $request->board_column_id;
        }

        $task->update($taskData);

        // Update assignments jika ada
        if ($request->has('user_ids')) {
            $task->assignments()->delete();
            foreach ($request->user_ids as $userId) {
                TaskAssignment::create([
                    'id' => Str::uuid()->toString(),
                    'task_id' => $task->id,
                    'user_id' => $userId,
                    'assigned_at' => now()
                ]);
            }
        }

        // Update labels jika ada
        if ($request->has('label_ids')) {
            $task->labels()->sync($request->label_ids);
        }

        DB::commit();

        // ✅ PERBAIKAN: Reload task dengan relasi lengkap
        $task->load([
            'assignments.user',
            'labels.color',
            'checklists',
            'attachments',
            'boardColumn',
            'creator'
        ]);

        // ✅ PERBAIKAN: Return response yang jelas
        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil diperbarui',
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'phase' => $task->phase,
                'description' => $task->description,
                'is_secret' => $task->is_secret,
                'status' => $task->status,
                'priority' => $task->priority,
                'start_datetime' => $task->start_datetime,
                'due_datetime' => $task->due_datetime,
                'board_column' => $task->boardColumn,
                'labels' => $task->labels,
                'assigned_members' => $task->assignments->map(fn($a) => [
                    'id' => $a->user->id,
                    'name' => $a->user->full_name,
                    'email' => $a->user->email,
                    'avatar' => $a->user->avatar
                ]),
                'checklists' => $task->checklists,
                'attachments' => $task->attachments
            ]
        ], 200); // ✅ Explicit 200 status

    } catch (\Exception $e) {
        DB::rollBack();

        Log::error('Error updating task detail:', [
            'task_id' => $taskId,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Gagal memperbarui tugas: ' . $e->getMessage()
        ], 500);
    }
}

    // ✅ NEW: Update checklist item
    public function updateChecklistItem(Request $request, $checklistId)
    {
        try {
            $checklist = Checklist::findOrFail($checklistId);
            $task = Task::findOrFail($checklist->task_id);
            $user = Auth::user();

            // Validasi akses
            if (!$this->canAccessWorkspace($task->workspace_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke task ini'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|string|max:255',
                'is_done' => 'sometimes|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $checklist->update($request->only(['title', 'is_done']));

            return response()->json([
                'success' => true,
                'message' => 'Checklist berhasil diupdate',
                'checklist' => $checklist
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating checklist item: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate checklist: ' . $e->getMessage()
            ], 500);
        }
    }

    // ✅ NEW: Create checklist item untuk task
   // Di TaskController.php - method createChecklistForTask
public function createChecklistForTask(Request $request, $taskId)
{
    try {
        $task = Task::findOrFail($taskId);
        $user = Auth::user();

        // Validasi akses
        if (!$this->canAccessWorkspace($task->workspace_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke task ini'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'is_done' => 'boolean' // ✅ TAMBAHKAN validasi untuk is_done
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Hitung posisi terakhir (handle null)
        $lastPosition = Checklist::where('task_id', $taskId)->max('position');
        $newPosition = ($lastPosition !== null) ? $lastPosition + 1 : 0;

        $checklist = Checklist::create([
            'id' => Str::uuid()->toString(),
            'task_id' => $taskId,
            'title' => $request->title,
            'is_done' => $request->is_done ?? false, // ✅ TERIMA is_done dari request
            'position' => $newPosition
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Checklist berhasil ditambahkan',
            'checklist' => $checklist
        ]);
    } catch (\Exception $e) {
        Log::error('Error creating checklist for task: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Gagal menambahkan checklist: ' . $e->getMessage()
        ], 500);
    }
}


    // Di TaskController - tambahkan method untuk update attachments
    public function updateTaskAttachments(Request $request, $taskId)
    {
        try {
            $task = Task::findOrFail($taskId);
            $user = Auth::user();

            if (!$this->canAccessWorkspace($task->workspace_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke task ini'
                ], 403);
            }

            $request->validate([
                'attachment_ids' => 'array',
                'attachment_ids.*' => 'exists:attachments,id'
            ]);

            // Update attachments yang terkait dengan task ini
            Attachment::where('attachable_type', 'App\\Models\\Task')
                ->where('attachable_id', $taskId)
                ->update(['attachable_id' => null]);

            if (!empty($request->attachment_ids)) {
                Attachment::whereIn('id', $request->attachment_ids)
                    ->update(['attachable_id' => $taskId]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Lampiran berhasil diupdate'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating task attachments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate lampiran'
            ], 500);
        }
    }



    // ✅ NEW: Update task title
    public function updateTaskTitle(Request $request, $taskId)
    {
        try {
            $task = Task::findOrFail($taskId);
            $user = Auth::user();

            // Validasi akses
            if (!$this->canAccessWorkspace($task->workspace_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke task ini'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $task->update(['title' => $request->title]);

            return response()->json([
                'success' => true,
                'message' => 'Judul tugas berhasil diperbarui',
                'task' => $task
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating task title: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui judul tugas'
            ], 500);
        }
    }

    // ✅ NEW: Add attachment to task
   public function addAttachmentToTask(Request $request, $taskId)
{
    $request->validate([
        'file' => 'required|file|max:10240',
    ]);

    try {
        $task = Task::findOrFail($taskId);
        $user = Auth::user();

        // Validasi akses
        if (!$this->canAccessWorkspace($task->workspace_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke task ini'
            ], 403);
        }

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
        $originalName = $file->getClientOriginalName();
        $fileName = time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

        // Simpan file
        $path = $file->storeAs('attachments', $fileName, 'public');

        // ✅ PERBAIKAN: Buat attachment tanpa file_type
        $attachment = new Attachment();
        $attachment->id = Str::uuid()->toString();
        $attachment->attachable_type = 'App\\Models\\Task';
        $attachment->attachable_id = $taskId;
        $attachment->file_url = $path;
        $attachment->file_name = $originalName;
        $attachment->file_size = $file->getSize();
        $attachment->uploaded_by = $user->id;
        $attachment->uploaded_at = now();

        $attachment->save();

        $attachment->load('uploader');

        return response()->json([
            'success' => true,
            'attachment' => [
                'id' => $attachment->id,
                'file_url' => $path,
                'file_name' => $originalName,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'uploaded_at' => $attachment->uploaded_at->toISOString()
            ],
            'message' => 'File berhasil diupload'
        ]);
    } catch (\Exception $e) {
        Log::error('Error adding attachment to task: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());

        return response()->json([
            'success' => false,
            'message' => 'Gagal upload file: ' . $e->getMessage()
        ], 500);
    }
}

    // ✅ NEW: Update task labels dengan modal label yang sama seperti tambah tugas
    // Di TaskController - pastikan method ini sudah ada
    public function updateTaskLabels(Request $request, $taskId)
    {
        $request->validate([
            'label_ids' => 'required|array',
            'label_ids.*' => 'exists:labels,id'
        ]);

        try {
            $task = Task::findOrFail($taskId);
            $user = Auth::user();

            // Validasi akses
            if (!$this->canAccessWorkspace($task->workspace_id)) {
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
            Log::error('Error updating task labels: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate label tugas: ' . $e->getMessage()
            ], 500);
        }
    }

    // ✅ NEW: Comment methods for tasks
    // Di TaskController - perbaiki method storeTaskComment
    public function getTaskComments($taskId)
    {
        $task = Task::findOrFail($taskId);

        $comments = $task->comments()
            ->with(['user', 'replies.user'])
            ->whereNull('parent_comment_id')
            ->orderBy('created_at', 'desc')
            ->get();

        // return comments as JSON
        return response()->json([
            'success' => true,
            'comments' => $comments
        ]);
    }

    // --- Store a new comment or reply ---
    public function storeTaskComment(Request $request, $taskId)
    {
        $request->validate([
            'content' => 'required|string',
            'parent_comment_id' => 'nullable|exists:comments,id',
            // optional: 'id' (pre-generated UUID) if frontend provides it
            'id' => 'nullable|string'
        ]);

        $task = Task::findOrFail($taskId);

        $comment = Comment::create([
            'id' => $request->input('id') ?? Str::uuid()->toString(),
            'user_id' => Auth::id(),
            'content' => $request->content,
            'commentable_id' => $task->id,
            'commentable_type' => Task::class,
            'parent_comment_id' => $request->parent_comment_id ?? null,
        ]);

        // optional: attach any pre-uploaded attachments that have attachable_type = Comment and attachable_id = pre-generated id
        // (frontend can upload files before comment store using the pre-generated id)
        if ($request->filled('id')) {
            // Attachments that used this id as attachable_id are already saved by upload endpoint.
            // No DB action required here unless you want to re-link / change attachable_type.
        }

        $comment->load('user');

        return response()->json([
            'success' => true,
            'comment' => $comment
        ]);
    }

    // --- Upload file/image for comment or other attachable ---
    /**
     * Upload file/image untuk komentar
     */
    public function uploadCommentFile(Request $request)
    {
        try {
            if (!$request->hasFile('upload')) {
                return response()->json(['error' => 'No file uploaded'], 400);
            }

            $file = $request->file('upload');
            $extension = strtolower($file->getClientOriginalExtension());

            // Validasi tipe file
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $documentExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip', 'rar', 'ppt', 'pptx'];

            $allowedExtensions = array_merge($imageExtensions, $documentExtensions);

            if (!in_array($extension, $allowedExtensions)) {
                return response()->json(['error' => 'File type not allowed'], 400);
            }

            // Validasi ukuran file (max 10MB)
            if ($file->getSize() > 10 * 1024 * 1024) {
                return response()->json(['error' => 'File size exceeds 10MB'], 400);
            }

            // Tentukan folder berdasarkan tipe file
            $folder = in_array($extension, $imageExtensions)
                ? 'uploads/comment_images'
                : 'uploads/comment_files';

            // Generate unique filename
            $fileName = time() . '_' . Str::random(8) . '.' . $extension;
            $filePath = $file->storeAs($folder, $fileName, 'public');
            $fileUrl = asset('storage/' . $filePath);

            // Simpan ke attachments table jika ada attachable_id
            $attachableId = $request->input('attachable_id');
            $attachableType = $request->input('attachable_type', 'App\\Models\\Comment');

            if ($attachableId) {
                Attachment::create([
                    'id' => Str::uuid()->toString(),
                    'attachable_type' => $attachableType,
                    'attachable_id' => $attachableId,
                    'file_url' => $filePath,
                    'uploaded_by' => Auth::id(),
                    'uploaded_at' => now(),
                ]);

                Log::info('Attachment created for comment', [
                    'attachable_id' => $attachableId,
                    'file_url' => $filePath
                ]);
            }

            return response()->json([
                'uploaded' => true,
                'url' => $fileUrl,
                'fileName' => $fileName,
                'fileType' => in_array($extension, $imageExtensions) ? 'image' : 'document'
            ]);
        } catch (\Exception $e) {
            Log::error('Error uploading comment file: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    // Tambahkan method ini di TaskController.php
   // Di TaskController.php - tambahkan method ini

    /**
     * Get phase date range and duration
     */
    /**
 * ✅ FIXED: Get timeline data dengan case-insensitive grouping
 * Phase yang sama secara case-insensitive dikelompokkan sebagai satu phase
 * Perbedaan 1 huruf = phase berbeda
 */
/**
 * ✅ FIXED: Get timeline data dengan handling phase kosong
 */
public function getTimelineData($workspaceId)
{
    try {
        $user = Auth::user();

        // Validasi akses workspace
        if (!$this->canAccessWorkspace($workspaceId)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke workspace ini'
            ], 403);
        }

        // Ambil semua tasks dari workspace
        $tasks = Task::where('workspace_id', $workspaceId)
            ->with(['assignments.user', 'boardColumn'])
            ->get();

        // ✅ DEBUG: Log semua phase yang ada di database
        Log::info('=== TIMELINE DEBUG START ===');
        Log::info('Total tasks: ' . $tasks->count());
        Log::info('All tasks with phases:', $tasks->map(function($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'phase_original' => $task->phase,
                'phase_normalized' => strtolower(trim($task->phase ?? '')),
                'status' => $task->status
            ];
        })->toArray());

        // Jika tidak ada tasks
        if ($tasks->isEmpty()) {
            return response()->json([
                'success' => true,
                'timeline_data' => [],
                'total_phases' => 0,
                'total_tasks' => 0,
                'completed_tasks' => 0,
                'message' => 'Tidak ada tugas di workspace ini'
            ]);
        }

        // ✅ PERBAIKAN UTAMA: Group tasks dengan lebih hati-hati
        $phaseGroups = [];

        foreach ($tasks as $task) {
            if (!$task->phase) {
                // Jika phase kosong, gunakan default
                $task->phase = 'Uncategorized';
            }

            // Normalisasi untuk grouping: lowercase dan trim
            $originalPhaseName = trim($task->phase);
            $normalizedKey = strtolower($originalPhaseName);

            // Handle empty phase name
            if (empty($normalizedKey)) {
                $originalPhaseName = 'Uncategorized';
                $normalizedKey = 'uncategorized';
            }

            // Jika phase belum ada di groups, buat entry baru
            if (!isset($phaseGroups[$normalizedKey])) {
                // Gunakan display name yang konsisten
                $displayName = $this->getDisplayPhaseName($originalPhaseName);

                $phaseGroups[$normalizedKey] = [
                    'original_name' => $displayName,
                    'normalized_key' => $normalizedKey,
                    'tasks' => [],
                    'total_tasks' => 0,
                    'completed_tasks' => 0,
                    'progress_percentage' => 0,
                    'start_date' => null,
                    'end_date' => null,
                    'duration' => 0
                ];
            }

            // Tambahkan task ke group
            $phaseGroups[$normalizedKey]['tasks'][] = $task;
            $phaseGroups[$normalizedKey]['total_tasks']++;

            // Hitung tugas yang selesai
            if ($task->status === 'done') {
                $phaseGroups[$normalizedKey]['completed_tasks']++;
            }
        }

        // ✅ DEBUG: Log hasil grouping
        Log::info('Phase groups after processing:', array_map(function($group) {
            return [
                'key' => $group['normalized_key'],
                'name' => $group['original_name'],
                'task_count' => $group['total_tasks'],
                'tasks' => array_map(function($task) {
                    return $task->id . ': ' . $task->title;
                }, $group['tasks'])
            ];
        }, $phaseGroups));

        // ✅ PERBAIKAN: Hitung progress dan date range untuk setiap phase
        $durations = [];

        foreach ($phaseGroups as $normalizedKey => &$phase) {
            // Progress percentage
            $phase['progress_percentage'] = $phase['total_tasks'] > 0
                ? round(($phase['completed_tasks'] / $phase['total_tasks']) * 100)
                : 0;

            // Date range calculation
            $dateRange = $this->calculatePhaseDateRange($phase['tasks']);

            $phase['start_date'] = $dateRange['start_date'];
            $phase['end_date'] = $dateRange['end_date'];
            $phase['duration'] = $dateRange['duration'];

            if ($dateRange['duration'] > 0) {
                $durations[] = $dateRange['duration'];
            }
        }

        unset($phase); // Unset reference untuk menghindari bug

        // Hitung durasi maksimum untuk scaling
        $maxDuration = !empty($durations) ? max($durations) : 1;

        // ✅ PERBAIKAN CRITICAL: Format timeline data dengan cara yang benar
        $timelineData = [];

        foreach ($phaseGroups as $normalizedKey => $phase) {
            // Skip phase tanpa tasks (safety check)
            if ($phase['total_tasks'] === 0) {
                Log::warning("Skipping phase '{$phase['original_name']}' with 0 tasks");
                continue;
            }

            // Hitung width percentage
            $duration_percentage = $phase['duration'] > 0
                ? min(($phase['duration'] / $maxDuration) * 100, 100)
                : 5;

            // Minimal 10% untuk phase yang memiliki durasi
            if ($phase['duration'] > 0 && $duration_percentage < 10) {
                $duration_percentage = 10;
            }

            $timelineData[] = [
                'id' => null, // Akan di-set setelah sorting
                'name' => $phase['original_name'],
                'normalized_key' => $normalizedKey,
                'total_tasks' => $phase['total_tasks'],
                'completed_tasks' => $phase['completed_tasks'],
                'progress_percentage' => $phase['progress_percentage'],
                'start_date' => $phase['start_date'],
                'end_date' => $phase['end_date'],
                'duration' => $phase['duration'],
                'duration_percentage' => $duration_percentage,
                'tasks' => $phase['tasks'] // Simpan dulu object tasks
            ];
        }

        // ✅ PERBAIKAN: Sort phases by start date (yang paling awal dulu)
        usort($timelineData, function($a, $b) {
            // Handle null dates
            if (!$a['start_date'] && !$b['start_date']) return 0;
            if (!$a['start_date']) return 1; // Yang null di akhir
            if (!$b['start_date']) return -1; // Yang null di akhir

            // Convert to timestamp for comparison
            $timeA = strtotime($a['start_date']);
            $timeB = strtotime($b['start_date']);

            // Ascending order (earliest first)
            return $timeA - $timeB;
        });

        // ✅ PERBAIKAN: Set IDs dan format tasks array setelah sorting
        foreach ($timelineData as $index => &$phaseItem) {
            $phaseItem['id'] = $index + 1;

            // Format tasks array
            $phaseItem['tasks'] = array_map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'status' => $task->status,
                    'is_done' => $task->status === 'done',
                    'start_datetime' => $task->start_datetime,
                    'due_datetime' => $task->due_datetime,
                    'assignees' => $task->assignments->map(function ($assignment) {
                        return [
                            'name' => $assignment->user->full_name,
                            'avatar' => $assignment->user->avatar ?: 'https://i.pravatar.cc/32?img=' . rand(1, 70)
                        ];
                    })->toArray()
                ];
            }, $phaseItem['tasks']);
        }

        unset($phaseItem); // Unset reference

        // ✅ DEBUG: Check for duplicates
        $uniqueCheck = [];
        $duplicates = [];
        foreach ($timelineData as $phase) {
            $key = $phase['normalized_key'];
            if (isset($uniqueCheck[$key])) {
                $duplicates[] = $phase['name'];
            }
            $uniqueCheck[$key] = true;
        }

        if (!empty($duplicates)) {
            Log::warning('Duplicate phases found in timeline data:', $duplicates);
        }

        // ✅ DEBUG: Log final timeline data
        Log::info('Final timeline data count: ' . count($timelineData));
        Log::info('Final timeline phases:', array_map(function($phase) {
            return [
                'id' => $phase['id'],
                'name' => $phase['name'],
                'normalized_key' => $phase['normalized_key'],
                'total_tasks' => $phase['total_tasks'],
                'start_date' => $phase['start_date'],
                'end_date' => $phase['end_date']
            ];
        }, $timelineData));

        Log::info('=== TIMELINE DEBUG END ===');

        return response()->json([
            'success' => true,
            'timeline_data' => $timelineData,
            'total_phases' => count($timelineData),
            'total_tasks' => $tasks->count(),
            'completed_tasks' => $tasks->where('status', 'done')->count(),
            'max_duration' => $maxDuration,
            'debug_info' => [
                'phase_count' => count($timelineData),
                'duplicates_found' => $duplicates
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('Error getting timeline data: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        return response()->json([
            'success' => false,
            'message' => 'Gagal mengambil data timeline: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * ✅ PERBAIKAN: Method untuk membersihkan data phase di database
 * Jalankan sekali untuk fix data
 */
public function cleanupPhases($workspaceId)
{
    try {
        $tasks = Task::where('workspace_id', $workspaceId)
            ->where(function($query) {
                $query->whereNull('phase')
                    ->orWhere('phase', '')
                    ->orWhereRaw("TRIM(phase) = ''");
            })
            ->get();

        $updates = [];

        foreach ($tasks as $task) {
            $oldPhase = $task->phase;
            $newPhase = 'Uncategorized';

            $updates[] = [
                'task_id' => $task->id,
                'title' => $task->title,
                'old_phase' => $oldPhase ?? '(NULL)',
                'new_phase' => $newPhase
            ];

            $task->phase = $newPhase;
            $task->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Phase cleanup completed',
            'updated_count' => count($updates),
            'updates' => $updates
        ]);

    } catch (\Exception $e) {
        Log::error('Error cleaning up phases: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Gagal membersihkan phase: ' . $e->getMessage()
        ], 500);
    }
}




private function getDisplayPhaseName($originalPhaseName)
{
    if (empty(trim($originalPhaseName))) {
        return 'Uncategorized';
    }

    // Gunakan ucwords dengan delimiter spasi dan strip_tags untuk safety
    $displayName = ucwords(strtolower(trim(strip_tags($originalPhaseName))));

    return $displayName;
}

/**
 * ✅ PERBAIKAN: Method untuk menghitung date range
 */
private function calculatePhaseDateRange($tasks)
{
    if (empty($tasks)) {
        return [
            'start_date' => null,
            'end_date' => null,
            'duration' => 0
        ];
    }

    $startDates = [];
    $endDates = [];

    foreach ($tasks as $task) {
        if ($task->start_datetime) {
            try {
                $startDates[] = \Carbon\Carbon::parse($task->start_datetime);
            } catch (\Exception $e) {
                Log::warning("Invalid start_datetime for task {$task->id}: {$task->start_datetime}");
            }
        }

        if ($task->due_datetime) {
            try {
                $endDates[] = \Carbon\Carbon::parse($task->due_datetime);
            } catch (\Exception $e) {
                Log::warning("Invalid due_datetime for task {$task->id}: {$task->due_datetime}");
            }
        }
    }

    // Jika tidak ada tanggal valid
    if (empty($startDates) && empty($endDates)) {
        return [
            'start_date' => null,
            'end_date' => null,
            'duration' => 0
        ];
    }

    // Tentukan earliest start dan latest end
    $earliestStart = !empty($startDates) ? min($startDates) : null;
    $latestEnd = !empty($endDates) ? max($endDates) : null;

    // Fallback logic
    if ($earliestStart && !$latestEnd) {
        $latestEnd = $earliestStart;
    }
    if (!$earliestStart && $latestEnd) {
        $earliestStart = $latestEnd;
    }

    // Calculate duration
    $duration = 0;
    if ($earliestStart && $latestEnd) {
        $duration = $earliestStart->diffInDays($latestEnd) + 1;
    }

    return [
        'start_date' => $earliestStart ? $earliestStart->toDateTimeString() : null,
        'end_date' => $latestEnd ? $latestEnd->toDateTimeString() : null,
        'duration' => max(0, $duration)
    ];
}


// TaskController.php - method untuk debug
public function debugPhases($workspaceId)
{
    $tasks = Task::where('workspace_id', $workspaceId)
        ->select('id', 'title', 'phase', 'status')
        ->get();

    $phases = $tasks->groupBy(function($task) {
        return strtolower(trim($task->phase ?? ''));
    })->map(function($group) {
        return [
            'display_name' => ucwords(strtolower(trim($group->first()->phase ?? ''))),
            'task_count' => $group->count(),
            'tasks' => $group->map(function($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'phase_original' => $task->phase,
                    'status' => $task->status
                ];
            })->values()
        ];
    });

    return response()->json([
        'success' => true,
        'phases' => $phases,
        'total_phases' => $phases->count(),
        'total_tasks' => $tasks->count()
    ]);
}

/**
 * ✅ NEW: Helper method untuk standardize phase name
 * Mengubah variasi nama phase menjadi format standar
 */
// private function getStandardizedPhaseName($phaseName)
// {
//     // Mapping nama phase yang umum ke format standar
//     $standardNames = [
//         'design' => 'Design',
//         'desain' => 'Design',
//         'development' => 'Development',
//         'develop' => 'Development',
//         'testing' => 'Testing',
//         'test' => 'Testing',
//         'deployment' => 'Deployment',
//         'deploy' => 'Deployment',
//         'planning' => 'Planning',
//         'perencanaan' => 'Planning',
//         'analysis' => 'Analysis',
//         'analisis' => 'Analysis',
//         'analisa' => 'Analysis'
//     ];

//     // Normalize input
//     $normalized = strtolower(
//         trim(
//             preg_replace('/\s+/', ' ',
//                 preg_replace('/[^a-zA-Z0-9\s]/', '', $phaseName)
//             )
//         )
//     );

//     // Cek apakah ada di mapping
//     if (isset($standardNames[$normalized])) {
//         return $standardNames[$normalized];
//     }

//     // Jika tidak ada, gunakan Title Case dari input asli
//     return ucwords(strtolower(trim($phaseName)));
// }



// Di App\Http\Controllers\TaskController

/**
 * Delete task (hanya untuk yang punya akses)
 */
public function deleteTask($taskId)
{
    try {
        $task = Task::findOrFail($taskId);
        $user = Auth::user();

        // ✅ VALIDASI: Gunakan method helper untuk cek akses
        if (!$this->canAccessWorkspace($task->workspace_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke task ini'
            ], 403);
        }

        // Cek apakah user memiliki izin untuk menghapus
        // SuperAdmin/Administrator/Admin/Manager bisa hapus
        // Creator juga bisa hapus tugasnya sendiri
        $userCompany = $user->userCompanies()
            ->where('company_id', session('active_company_id'))
            ->with('role')
            ->first();

        $userRole = $userCompany?->role?->name ?? 'Member';
        $isCreator = $task->created_by === $user->id;

        if (!in_array($userRole, ['SuperAdmin', 'Administrator', 'Admin', 'Manager']) && !$isCreator) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk menghapus tugas ini'
            ], 403);
        }

        DB::beginTransaction();

        // Log sebelum menghapus untuk audit trail
        Log::info('Deleting task', [
            'task_id' => $task->id,
            'title' => $task->title,
            'deleted_by' => $user->id,
            'deleted_at' => now()
        ]);

        // Hapus task (akan trigger soft delete karena ada SoftDeletes trait)
        $task->delete();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil dihapus'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error deleting task: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Gagal menghapus tugas: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Permanently delete task (hanya untuk SuperAdmin/Administrator)
 */
public function forceDeleteTask($taskId)
{
    try {
        $task = Task::withTrashed()->findOrFail($taskId);
        $user = Auth::user();

        // Hanya SuperAdmin/Administrator yang bisa permanent delete
        $userCompany = $user->userCompanies()
            ->where('company_id', session('active_company_id'))
            ->with('role')
            ->first();

        $userRole = $userCompany?->role?->name ?? 'Member';

        if (!in_array($userRole, ['SuperAdmin', 'Administrator'])) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya SuperAdmin/Administrator yang dapat menghapus permanen'
            ], 403);
        }

        DB::beginTransaction();

        // Delete all related data
        $task->assignments()->delete();
        $task->checklists()->delete();
        $task->labels()->detach();

        // Delete attachments files
        foreach ($task->attachments as $attachment) {
            if (Storage::disk('public')->exists($attachment->file_url)) {
                Storage::disk('public')->delete($attachment->file_url);
            }
            $attachment->delete();
        }

        // Delete comments
        $task->comments()->delete();

        // Force delete task
        $task->forceDelete();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil dihapus permanen'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error force deleting task: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Gagal menghapus permanen: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Restore deleted task (hanya untuk SuperAdmin/Administrator/Admin)
 */
public function restoreTask($taskId)
{
    try {
        $task = Task::withTrashed()->findOrFail($taskId);
        $user = Auth::user();

        // Validasi akses workspace
        if (!$this->canAccessWorkspace($task->workspace_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke task ini'
            ], 403);
        }

        // Hanya admin yang bisa restore
        $userCompany = $user->userCompanies()
            ->where('company_id', session('active_company_id'))
            ->with('role')
            ->first();

        $userRole = $userCompany?->role?->name ?? 'Member';

        if (!in_array($userRole, ['SuperAdmin', 'Administrator', 'Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya admin yang dapat mengembalikan tugas'
            ], 403);
        }

        $task->restore();

        return response()->json([
            'success' => true,
            'message' => 'Tugas berhasil dikembalikan'
        ]);

    } catch (\Exception $e) {
        Log::error('Error restoring task: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Gagal mengembalikan tugas: ' . $e->getMessage()
        ], 500);
    }
}



/**
 * ✅ Hapus kolom kanban custom
 */
public function deleteCustomColumn($columnId)
{
    try {
        $user = Auth::user();
        $column = BoardColumn::with('workspace')->findOrFail($columnId);

        // ✅ VALIDASI: Gunakan method helper untuk cek akses workspace
        if (!$this->canAccessWorkspace($column->workspace_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke workspace ini'
            ], 403);
        }

        // ✅ Cegah penghapusan kolom default
        $defaultColumns = ['To Do List', 'Dikerjakan', 'Selesai', 'Batal'];

        if (in_array($column->name, $defaultColumns)) {
            return response()->json([
                'success' => false,
                'message' => 'Kolom default tidak dapat dihapus'
            ], 400);
        }

        // ✅ Pastikan tidak ada tugas di kolom ini sebelum dihapus
        $taskCount = Task::where('board_column_id', $columnId)->count();

        if ($taskCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus kolom yang masih berisi tugas. Pindahkan semua tugas terlebih dahulu.'
            ], 400);
        }

        DB::beginTransaction();

        // Log sebelum menghapus
        Log::info('Deleting custom column', [
            'column_id' => $column->id,
            'column_name' => $column->name,
            'workspace_id' => $column->workspace_id,
            'deleted_by' => $user->id
        ]);

        // Hapus kolom
        $column->delete();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Kolom custom berhasil dihapus'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error deleting custom column: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Gagal menghapus kolom: ' . $e->getMessage()
        ], 500);
    }
}


// Method alternatif di TaskController
public function deleteColumnWithTasksTransfer(Request $request, $columnId)
{
    $request->validate([
        'target_column_id' => 'required|exists:board_columns,id'
    ]);

    try {
        $user = Auth::user();
        $column = BoardColumn::with('workspace')->findOrFail($columnId);
        $targetColumn = BoardColumn::findOrFail($request->target_column_id);

        // Validasi akses
        if (!$this->canAccessWorkspace($column->workspace_id) ||
            !$this->canAccessWorkspace($targetColumn->workspace_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak'
            ], 403);
        }

        // Pastikan kedua kolom dalam workspace yang sama
        if ($column->workspace_id !== $targetColumn->workspace_id) {
            return response()->json([
                'success' => false,
                'message' => 'Kolom target harus dalam workspace yang sama'
            ], 400);
        }

        DB::beginTransaction();

        // Pindahkan semua tugas ke kolom target
        Task::where('board_column_id', $columnId)
            ->update(['board_column_id' => $request->target_column_id]);

        // Hapus kolom
        $column->delete();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Kolom berhasil dihapus dan tugas dipindahkan'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error deleting column with transfer: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Gagal menghapus kolom: ' . $e->getMessage()
        ], 500);
    }
}



// ✅ NEW: Create checklist untuk task yang sudah ada
public function createChecklistForExistingTask(Request $request, $taskId)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'is_done' => 'boolean'
    ]);

    try {
        $task = Task::findOrFail($taskId);
        $user = Auth::user();

        // ✅ VALIDASI: Gunakan method helper untuk cek akses
        if (!$this->canAccessWorkspace($task->workspace_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke task ini'
            ], 403);
        }

        // Hitung posisi terakhir
        $lastPosition = Checklist::where('task_id', $taskId)->max('position');

        $checklist = Checklist::create([
            'id' => Str::uuid()->toString(),
            'task_id' => $taskId,
            'title' => $request->title,
            'is_done' => $request->is_done ?? false,
            'position' => ($lastPosition ? $lastPosition + 1 : 0)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Checklist berhasil ditambahkan',
            'checklist' => $checklist
        ]);
    } catch (\Exception $e) {
        Log::error('Error creating checklist for existing task: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Gagal menambahkan checklist: ' . $e->getMessage()
        ], 500);
    }
}



// ✅ NEW: Get all checklists for task
public function getAllChecklists($taskId)
{
    try {
        $task = Task::findOrFail($taskId);
        $user = Auth::user();

        if (!$this->canAccessWorkspace($task->workspace_id)) {
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
        Log::error('Error getting all checklists: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Gagal mengambil data checklist'
        ], 500);
    }
}



}
