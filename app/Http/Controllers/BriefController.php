<?php

namespace App\Http\Controllers;


use App\Services\Document\DocumentParserService;
use App\Services\AI\AIService;
use App\Models\Company;
use App\Models\Workspace;
use App\Models\BoardColumn;
use App\Models\UserWorkspace;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\Role;
use App\Models\File;
use App\Models\Decision;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BriefController extends Controller
{
    public function __construct(
        protected DocumentParserService $documentParser,
        protected AIService $aiService
    ) {}

    public function index() 
    {
        return view('upload-brief');
    }

       public function brief(Workspace $workspace)
    {
        return view('ai-brief', compact('workspace'));
    }

    public function uploadbrief(Workspace $workspace)
    {
        return view('upload-brief', compact('workspace'));
    }
    
    public function upload(Request $request) 
    {
        $request->validate([ 
            'documents' => ['required', 'array'],
            'documents.*' => ['required', 'file'],
        ]);

        $activeCompanyId = session('active_company_id');
        $workspaceId = $request->input('workspace_id');

        try {
            // 1. Simpan file ke Storage dan Database (tabel files)
            $uploadedFilesMapping = [];
            foreach ($request->file('documents') as $file) {
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $uploadedBy = auth()->id();

                // Dapatkan nama unik agar tidak bentrok
                $newName = $originalName;
                $counter = 1;

                // Setup query scope untuk mencari kecocokan nama file yang bertabrakan
                $query = File::query();
                if ($workspaceId) {
                    $query->where('workspace_id', $workspaceId);
                } else {
                    $query->whereNull('workspace_id')
                          ->where('company_id', $activeCompanyId);
                }

                while ((clone $query)->where('file_name', $newName . '.' . $extension)->exists()) {
                    $newName = $originalName . '(' . $counter . ')';
                    $counter++;
                }

                $finalName = $newName . '.' . $extension;
                
                // Simpan fisik
                $path = $file->storeAs('files', $finalName, 'public');

                // Simpan DB
                $fileModel = File::create([
                    'workspace_id' => $workspaceId ?: null,
                    'company_id' => $activeCompanyId ?: null,
                    'uploaded_by' => $uploadedBy,
                    'file_name' => $finalName,
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'file_type' => $extension,
                    'file_url' => asset('storage/' . $path),
                    'is_private' => false,
                    'uploaded_at' => now(),
                ]);

                // Simpan mapping nama asli file -> file id
                $uploadedFilesMapping[$file->getClientOriginalName()] = $fileModel->id;
            }

            // 2. Parse & Normalize documents
            $documents = $this->documentParser->parse(
                $request->file('documents')
            );

            // 3. Generate Brief using AI
            $briefData = $this->aiService->generateBrief($documents);

            // Save brief draft & files mapping to session
            session(['brief_draft' => $briefData]);
            session(['brief_files_mapping' => $uploadedFilesMapping]);
            if ($workspaceId) {
                session(['brief_workspace_id' => $workspaceId]);
            } else {
                session()->forget('brief_workspace_id');
            }

            return redirect()->route('brief.review');

        } catch (\Exception $e) {
            Log::error('AI Brief parsing failed: ' . $e->getMessage());
            return redirect()->back()->with('alert', [
                'icon' => 'error',
                'title' => 'Gagal',
                'text' => 'Gagal memproses dokumen brief: ' . $e->getMessage(),
            ]);
        }
    }

    public function review()
    {
        $briefData = session('brief_draft');
        if (!$briefData) {
            return redirect()->route('brief.index')->with('alert', [
                'icon' => 'warning',
                'title' => 'Perhatian',
                'text' => 'Silakan unggah dokumen brief terlebih dahulu.',
            ]);
        }

        // Fetch company members to populate owner selection dropdown
        $activeCompanyId = session('active_company_id');
        $company = Company::find($activeCompanyId);
        $members = $company ? $company->users()->where('user_companies.status_active', true)->get() : collect();

        return view('ai-brief', [
            'brief' => $briefData,
            'members' => $members,
            'briefWorkspaceId' => session('brief_workspace_id'),
        ]);
    }

    public function approve(Request $request)
    {
        $request->validate([
            'workspace_id' => ['nullable', 'exists:workspaces,id'],
            'project_name' => ['required', 'string', 'max:255'],
            'project_goal' => ['nullable', 'string'],
            'deliverables' => ['nullable', 'string'],
            'deadline' => ['nullable', 'string', 'max:255'],
            'tasks' => ['nullable', 'array'],
            'tasks.*.title' => ['required', 'string', 'max:255'],
            'tasks.*.description' => ['nullable', 'string'],
            'tasks.*.priority' => ['required', 'in:low,medium,high,urgent'],
            'tasks.*.deadline' => ['nullable', 'string', 'max:255'],
            'tasks.*.assignee_id' => ['nullable', 'exists:users,id'],
            'clarification_questions' => ['nullable', 'array'],
            'decisions' => ['nullable', 'array'],
            'decisions.*.title' => ['required', 'string', 'max:255'],
            'decisions.*.sources' => ['nullable', 'array'],
        ]);

        $activeCompanyId = session('active_company_id');
        if (!$activeCompanyId) {
            return redirect()->back()->with('error', 'Tidak ada perusahaan yang aktif.');
        }

        Log::info('[APPROVE DEBUG] workspace_id dari request: ' . ($request->workspace_id ?? 'null/kosong') . ' | filled: ' . ($request->filled('workspace_id') ? 'true' : 'false'));

        try {
            DB::beginTransaction();

            if ($request->filled('workspace_id')) {
                // JIKA WORKSPACE SUDAH ADA, GUNAKAN WORKSPACE INI TANPA MENGUBAH DETAILNYA
                $workspace = Workspace::findOrFail($request->workspace_id);
            } else {
                // JIKA WORKSPACE BELUM ADA, BUAT WORKSPACE BARU SEBAGAI PROYEK
                $workspace = Workspace::create([
                    'company_id' => $activeCompanyId,
                    'type' => 'Proyek',
                    'name' => $request->project_name,
                    'description' => $request->project_goal,
                    'created_by' => Auth::id()
                ]);

                // Set current workspace in session
                session([
                    'current_workspace_id' => $workspace->id,
                    'current_workspace_name' => $workspace->name
                ]);

                // Add the Creator to the Workspace as Manager
                $managerRole = Role::where('name', 'Manager')->first();
                $managerRoleId = $managerRole ? $managerRole->id : 'a688ef38-3030-45cb-9a4d-0407605bc322';

                UserWorkspace::create([
                    'user_id' => Auth::id(),
                    'workspace_id' => $workspace->id,
                    'roles_id' => $managerRoleId,
                    'status_active' => true,
                    'join_date' => now(),
                ]);
            }

            // Update workspace_id untuk file-file yang baru diunggah dalam sesi upload ini
            $filesMapping = session('brief_files_mapping') ?? [];
            if (!empty($filesMapping)) {
                File::whereIn('id', array_values($filesMapping))
                    ->update(['workspace_id' => $workspace->id]);
            }

            // Create Decisions
            if ($request->has('decisions')) {
                foreach ($request->decisions as $decisionData) {
                    $evidenceFileId = null;

                    // Cari file_id berdasarkan sources nama file yang cocok
                    if (!empty($decisionData['sources'])) {
                        foreach ($decisionData['sources'] as $sourceFilename) {
                            if (isset($filesMapping[$sourceFilename])) {
                                $evidenceFileId = $filesMapping[$sourceFilename];
                                break; // Ambil file pertama yang cocok
                            }
                        }
                    }

                    Decision::create([
                        'workspace_id' => $workspace->id,
                        'created_by' => Auth::id(),
                        'title' => $decisionData['title'],
                        'decision_date' => now(),
                        'evidence_file_id' => $evidenceFileId,
                        'is_validated' => false,
                    ]);
                }
            }

            // Find default board column (e.g. To Do List)
            $defaultColumn = BoardColumn::where('workspace_id', $workspace->id)
                ->where('name', 'like', '%To Do%')
                ->first();
            
            if (!$defaultColumn) {
                $defaultColumn = BoardColumn::where('workspace_id', $workspace->id)->first();
            }

            $defaultColumnId = $defaultColumn ? $defaultColumn->id : null;

            // Create tasks and assignments
            $memberRole = Role::where('name', 'Member')->first();
            $memberRoleId = $memberRole ? $memberRole->id : 'ed81bd39-9041-43b8-a504-bf743b5c2919';
            $newCreatedTaskIds = [];

            if ($request->has('tasks')) {
                foreach ($request->tasks as $taskData) {
                    // Check if assignee is member of workspace, if not add them
                    if (!empty($taskData['assignee_id'])) {
                        $exists = UserWorkspace::where('workspace_id', $workspace->id)
                            ->where('user_id', $taskData['assignee_id'])
                            ->exists();

                        if (!$exists) {
                            UserWorkspace::create([
                                'user_id' => $taskData['assignee_id'],
                                'workspace_id' => $workspace->id,
                                'roles_id' => $memberRoleId,
                                'status_active' => true,
                                'join_date' => now(),
                            ]);
                        }
                    }

                    // Parse deadline string to a valid ISO date for database compatibility
                    $parsedDate = $this->parseDateToIso($taskData['deadline'] ?? null);

                    // Create task
                    $task = Task::create([
                        'id' => Str::uuid()->toString(),
                        'workspace_id' => $workspace->id,
                        'created_by' => Auth::id(),
                        'title' => $taskData['title'],
                        'description' => $taskData['description'] ?? '',
                        'status' => 'todo',
                        'board_column_id' => $defaultColumnId,
                        'priority' => $taskData['priority'],
                        'due_datetime' => $parsedDate ? Carbon::parse($parsedDate)->endOfDay() : null,
                    ]);

                    $newCreatedTaskIds[] = $task->id;

                    // Assign user
                    if (!empty($taskData['assignee_id'])) {
                        TaskAssignment::create([
                            'task_id' => $task->id,
                            'user_id' => $taskData['assignee_id'],
                            'assigned_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();
            Log::info('DB Dipanggil cuk');

            return redirect()->route('kanban-tugas', $workspace->id)
                ->with('success', 'Proyek berhasil dibuat dari brief!')
                ->with('new_task_ids', $newCreatedTaskIds);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Approved brief import failed: ' . $e->getMessage());
            return redirect()->back()->with('alert', [
                'icon' => 'error',
                'title' => 'Gagal',
                'text' => 'Gagal membuat proyek: ' . $e->getMessage(),
            ])->withInput();
        }
    }

    /**
     * Funsi helper mengubah tanggal menjadi format yang diterima php
     */
    private function parseDateToIso(?string $dateString): ?string
    {
        if (empty($dateString)) {
            return null;
        }

        // Try standard parsing first
        try {
            return Carbon::parse($dateString)->toDateString();
        } catch (\Exception $e) {
            // Proceed to Indonesian localization parsing
        }

        $months = [
            'januari' => 'january', 'februari' => 'february', 'maret' => 'march',
            'april' => 'april', 'mei' => 'may', 'juni' => 'june',
            'juli' => 'july', 'agustus' => 'august', 'september' => 'september',
            'oktober' => 'october', 'november' => 'november', 'desember' => 'december'
        ];

        $cleanedString = strtolower($dateString);
        foreach ($months as $id => $en) {
            if (str_contains($cleanedString, $id)) {
                $cleanedString = str_replace($id, $en, $cleanedString);
                break;
            }
        }

        try {
            return Carbon::parse($cleanedString)->toDateString();
        } catch (\Exception $e) {
            Log::warning("Failed to parse deadline string: " . $dateString);
            return null;
        }
    }
       
 
}
