<?php

namespace App\Http\Controllers;


use App\Services\Document\DocumentParserService;
use App\Services\AI\AIService;
use App\Services\NotificationService;
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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BriefController extends Controller
{
    public function __construct(
        protected DocumentParserService $documentParser,
        protected AIService $aiService,
        protected NotificationService $notificationService
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

    public function workspaceTemplate(Workspace $workspace)
    {
        return view('template-brief', compact('workspace'));
    }

    public function template()
    {
        return view('template-brief');
    }

    public function saveTemplate(Request $request)
    {
        $request->validate([
            'template_name' => ['required', 'string', 'max:255'],
            'template_goal' => ['required', 'string'],
            'template_start_date' => ['nullable', 'string', 'max:255'],
            'template_end_date' => ['nullable', 'string', 'max:255'],
            'template_period' => ['nullable', 'string', 'max:255'],
            'template_phases' => ['nullable', 'string'],
            'template_tasks' => ['nullable', 'string'],
            'template_phase_rows' => ['nullable', 'string'],
            'template_deliverables' => ['nullable', 'string'],
            'template_scope' => ['nullable', 'string'],
            'template_roles' => ['nullable', 'string'],
            'template_budget' => ['nullable', 'string'],
        ]);

        $workspaceId = $request->input('workspace_id');

        session([
            'pending_template_brief' => [
                'name' => $request->input('template_name', 'Template Proyek'),
                'goal' => $request->input('template_goal', ''),
                'start_date' => $request->input('template_start_date', ''),
                'end_date' => $request->input('template_end_date', ''),
                'period' => $request->input('template_period', ''),
                'phases' => $request->input('template_phases', ''),
                'tasks' => $request->input('template_tasks', ''),
                'phase_rows' => $request->input('template_phase_rows', ''),
                'deliverables' => $request->input('template_deliverables', ''),
                'scope' => $request->input('template_scope', ''),
                'roles' => $request->input('template_roles', ''),
                'budget' => $request->input('template_budget', ''),
            ]
        ]);

        if ($workspaceId) {
            return redirect()->route('upload-brief', $workspaceId)->with('success', 'Template rencana kerja berhasil disimpan sebagai sumber konteks.');
        }

        return redirect()->route('brief.index')->with('success', 'Template rencana kerja berhasil disimpan sebagai sumber konteks.');
    }

    public function clearTemplate(Request $request)
    {
        session()->forget('pending_template_brief');
        $workspaceId = $request->input('workspace_id');

        if ($workspaceId) {
            return redirect()->route('upload-brief', $workspaceId);
        }

        return redirect()->route('brief.index');
    }

    public function upload(Request $request)
    {
        $hasPendingTemplate = session()->has('pending_template_brief');
        $isTemplate = $request->boolean('is_template') || $hasPendingTemplate;

        if (!$isTemplate && !$request->hasFile('documents')) {
            return redirect()->back()->with('alert', [
                'icon' => 'warning',
                'title' => 'Perhatian',
                'text' => 'Silakan pilih minimal satu berkas dokumen atau gunakan Template Rencana Kerja.',
            ]);
        }

        $activeCompanyId = session('active_company_id');
        $workspaceId = $request->input('workspace_id');

        try {
            $uploadedFilesMapping = [];
            $documents = [];

            if ($isTemplate) {
                // 1. Format konten teks template proyek
                $pending = session('pending_template_brief', []);
                $templateName = $request->filled('template_name') ? $request->input('template_name') : ($pending['name'] ?? 'Template Proyek');
                $templateGoal = $request->filled('template_goal') ? $request->input('template_goal') : ($pending['goal'] ?? '');
                $templateStartDate = $request->filled('template_start_date') ? $request->input('template_start_date') : ($pending['start_date'] ?? '');
                $templateEndDate = $request->filled('template_end_date') ? $request->input('template_end_date') : ($pending['end_date'] ?? '');
                $templatePeriod = $request->filled('template_period') ? $request->input('template_period') : ($pending['period'] ?? '');
                $templatePhases = $request->filled('template_phases') ? $request->input('template_phases') : ($pending['phases'] ?? '');
                $templateTasks = $request->filled('template_tasks') ? $request->input('template_tasks') : ($pending['tasks'] ?? '');
                $templateDeliverables = $request->filled('template_deliverables') ? $request->input('template_deliverables') : ($pending['deliverables'] ?? '');
                $templateScope = $request->filled('template_scope') ? $request->input('template_scope') : ($pending['scope'] ?? '');
                $templateRoles = $request->filled('template_roles') ? $request->input('template_roles') : ($pending['roles'] ?? '');
                $templateBudget = $request->filled('template_budget') ? $request->input('template_budget') : ($pending['budget'] ?? '');

                $periodInfo = !empty($templatePeriod) ? $templatePeriod : '';
                if (!empty($templateStartDate) || !empty($templateEndDate)) {
                    $dateDetails = [];
                    if (!empty($templateStartDate)) $dateDetails[] = "Tanggal Mulai: {$templateStartDate}";
                    if (!empty($templateEndDate)) $dateDetails[] = "Target Selesai: {$templateEndDate}";
                    $periodInfo .= (!empty($periodInfo) ? " (" . implode(', ', $dateDetails) . ")" : implode(', ', $dateDetails));
                }

                $textContent = "===== DOKUMEN KONTEKS BRIEF PROYEK =====\n\n"
                    . "NAMA / TIPE PROYEK: {$templateName}\n\n"
                    . "TUJUAN UTAMA PROYEK:\n{$templateGoal}\n\n"
                    . "PERIODE / DEADLINE PROYEK:\n{$periodInfo}\n\n"
                    . (!empty($templatePhases) ? "TAHAPAN / FASE PROYEK:\n{$templatePhases}\n\n" : "")
                    . (!empty($templateTasks) ? "RINCIAN TUGAS PER FASE:\n{$templateTasks}\n\n" : "")
                    . "OUTPUT AKHIR / DELIVERABLES:\n{$templateDeliverables}\n\n"
                    . (!empty($templateScope) ? "RUANG LINGKUP & DETAIL PEKERJAAN:\n{$templateScope}\n\n" : "")
                    . (!empty($templateRoles) ? "ROLE / ANGGOTA TIM TERLIBAT:\n{$templateRoles}\n\n" : "")
                    . (!empty($templateBudget) ? "CATATAN ANGGARAN & KEBUTUHAN KHUSUS:\n{$templateBudget}\n\n" : "")
                    . "========================================\n";

                $originalName = "Template Konteks - " . Str::slug($templateName, ' ');
                $extension = 'txt';
                $uploadedBy = auth()->id();

                // Dapatkan nama unik agar tidak bentrok
                $newName = $originalName;
                $counter = 1;

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
                $storagePath = 'files/' . Str::slug($newName, '_') . '_' . time() . '.' . $extension;

                // Simpan fisik file ke storage
                Storage::disk('public')->put($storagePath, $textContent);

                // Simpan DB
                $fileModel = File::create([
                    'workspace_id' => $workspaceId ?: null,
                    'company_id' => $activeCompanyId ?: null,
                    'uploaded_by' => $uploadedBy,
                    'file_name' => $finalName,
                    'file_path' => $storagePath,
                    'file_size' => strlen($textContent),
                    'file_type' => $extension,
                    'file_url' => asset('storage/' . $storagePath),
                    'is_private' => false,
                    'uploaded_at' => now(),
                ]);

                $uploadedFilesMapping[$finalName] = $fileModel->id;

                $documents = [
                    [
                        'filename' => $finalName,
                        'extension' => 'txt',
                        'mime_type' => 'text/plain',
                        'content' => $textContent,
                    ]
                ];

                // Jika pengguna juga mengunggah dokumen fisik pendukung (PDF, DOCX, TXT)
                if ($request->hasFile('documents')) {
                    foreach ($request->file('documents') as $file) {
                        $docOriginalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        $docExtension = $file->getClientOriginalExtension();

                        $docNewName = $docOriginalName;
                        $docCounter = 1;
                        $docQuery = File::query();
                        if ($workspaceId) {
                            $docQuery->where('workspace_id', $workspaceId);
                        } else {
                            $docQuery->whereNull('workspace_id')->where('company_id', $activeCompanyId);
                        }
                        while ((clone $docQuery)->where('file_name', $docNewName . '.' . $docExtension)->exists()) {
                            $docNewName = $docOriginalName . '(' . $docCounter . ')';
                            $docCounter++;
                        }
                        $docFinalName = $docNewName . '.' . $docExtension;
                        $docPath = $file->storeAs('files', $docFinalName, 'public');

                        $docModel = File::create([
                            'workspace_id' => $workspaceId ?: null,
                            'company_id' => $activeCompanyId ?: null,
                            'uploaded_by' => $uploadedBy,
                            'file_name' => $docFinalName,
                            'file_path' => $docPath,
                            'file_size' => $file->getSize(),
                            'file_type' => $docExtension,
                            'file_url' => asset('storage/' . $docPath),
                            'is_private' => false,
                            'uploaded_at' => now(),
                        ]);

                        $uploadedFilesMapping[$file->getClientOriginalName()] = $docModel->id;
                    }

                    // Parse dokumen pendukung dan gabungkan ke array $documents
                    $parsedUploadedDocs = $this->documentParser->parse($request->file('documents'));
                    $documents = array_merge($documents, $parsedUploadedDocs);
                }
            } else {
                // 1. Simpan file ke Storage dan Database (tabel files)
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
            }

            // 3. Generate Brief using AI
            $briefData = $this->aiService->generateBrief($documents);

            // Save brief draft & files mapping to session
            session(['brief_draft' => $briefData]);
            session(['brief_files_mapping' => $uploadedFilesMapping]);
            session()->forget('pending_template_brief');
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

        $deliverables = $briefData['summary']['deliverables'] ?? [];
        $deliverablesLabel = is_array($deliverables)
            ? implode(', ', $deliverables)
            : ($deliverables ?? '');

        // Available uploaded files for decision sources
        $availableFiles = collect($briefData['files'] ?? [])
            ->map(fn($f) => is_array($f) ? ($f['name'] ?? '') : (string) $f)
            ->filter()
            ->values()
            ->toArray();

        return view('ai-brief', [
            'brief' => $briefData,
            'members' => $members,
            'briefWorkspaceId' => session('brief_workspace_id'),
            'deliverablesLabel' => $deliverablesLabel,
            'availableFiles' => $availableFiles,
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
            'tasks.*.phase' => ['nullable', 'string', 'max:100'],
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

                    $decision = Decision::create([
                        'workspace_id' => $workspace->id,
                        'created_by' => Auth::id(),
                        'title' => $decisionData['title'],
                        'decision_date' => now(),
                        'evidence_file_id' => $evidenceFileId,
                        'is_validated' => false,
                    ]);

                    try {
                        $this->notificationService->notifyDecisionCreated($decision);
                    } catch (\Exception $e) {
                        Log::warning('Notification dispatch skipped (offline/broadcast unavailable): ' . $e->getMessage());
                    }
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

                    // Parse start_date & deadline string to a valid ISO date for database compatibility
                    $parsedStartDate = $this->parseDateToIso($taskData['start_date'] ?? null);
                    $parsedDate = $this->parseDateToIso($taskData['deadline'] ?? null);

                    // Determine phase
                    $taskPhase = !empty($taskData['phase']) ? $taskData['phase'] : null;
                    if ($taskPhase) {
                        Log::info("[PHASE TRACKER] Task '{$taskData['title']}' memiliki phase DARI GEMINI / FRONTEND: '{$taskPhase}'");
                    } elseif (!empty($taskData['title']) && (str_starts_with($taskData['title'], 'Klarifikasi') || str_starts_with($taskData['title'], 'Info Belum Ada'))) {
                        $taskPhase = 'Klarifikasi';
                        Log::info("[PHASE TRACKER] Task '{$taskData['title']}' disuntik phase DARI LOGIKA CONTROLLER FALLBACK: '{$taskPhase}'");
                    } else {
                        Log::info("[PHASE TRACKER] Task '{$taskData['title']}' tidak memiliki phase (NULL)");
                    }

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
                        'start_datetime' => $parsedStartDate ? Carbon::parse($parsedStartDate)->startOfDay() : null,
                        'due_datetime' => $parsedDate ? Carbon::parse($parsedDate)->endOfDay() : null,
                        'phase' => $taskPhase,
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
            'januari' => 'january',
            'februari' => 'february',
            'maret' => 'march',
            'april' => 'april',
            'mei' => 'may',
            'juni' => 'june',
            'juli' => 'july',
            'agustus' => 'august',
            'september' => 'september',
            'oktober' => 'october',
            'november' => 'november',
            'desember' => 'december'
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
