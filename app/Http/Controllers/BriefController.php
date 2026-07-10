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
    
    public function upload(Request $request) 
    {
        $request->validate([ 
            'documents' => ['required', 'array'],
            'documents.*' => ['required', 'file'],
        ]);

        try {
            // 1. Parse & Normalize documents
            $documents = $this->documentParser->parse(
                $request->file('documents')
            );

            // 2. Generate Brief using AI
            $briefData = $this->aiService->generateBrief($documents);

            // Save brief draft to session
            session(['brief_draft' => $briefData]);

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

        return view('brief-review', [
            'brief' => $briefData,
            'members' => $members,
        ]);
    }

    public function approve(Request $request)
    {
        $request->validate([
            'project_name' => ['required', 'string', 'max:255'],
            'project_goal' => ['nullable', 'string'],
            'deliverables' => ['nullable', 'string'],
            'deadline' => ['nullable', 'date'],
            'tasks' => ['nullable', 'array'],
            'tasks.*.title' => ['required', 'string', 'max:255'],
            'tasks.*.description' => ['nullable', 'string'],
            'tasks.*.priority' => ['required', 'in:low,medium,high,urgent'],
            'tasks.*.deadline' => ['nullable', 'date'],
            'tasks.*.assignee_id' => ['nullable', 'exists:users,id'],
            'clarification_questions' => ['nullable', 'array'],
        ]);

        $activeCompanyId = session('active_company_id');
        if (!$activeCompanyId) {
            return redirect()->back()->with('error', 'Tidak ada perusahaan yang aktif.');
        }

        try {
            DB::beginTransaction();

            // 1. Create the Workspace representing the Project
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

            // 2. Add the Creator to the Workspace as Manager
            $managerRole = Role::where('name', 'Manager')->first();
            $managerRoleId = $managerRole ? $managerRole->id : 'a688ef38-3030-45cb-9a4d-0407605bc322';

            UserWorkspace::create([
                'user_id' => Auth::id(),
                'workspace_id' => $workspace->id,
                'roles_id' => $managerRoleId,
                'status_active' => true,
                'join_date' => now(),
            ]);

            // 3. Find default board column (e.g. To Do List)
            $defaultColumn = BoardColumn::where('workspace_id', $workspace->id)
                ->where('name', 'like', '%To Do%')
                ->first();
            
            if (!$defaultColumn) {
                $defaultColumn = BoardColumn::where('workspace_id', $workspace->id)->first();
            }

            $defaultColumnId = $defaultColumn ? $defaultColumn->id : null;

            // 4. Create tasks and assignments
            $memberRole = Role::where('name', 'Member')->first();
            $memberRoleId = $memberRole ? $memberRole->id : 'ed81bd39-9041-43b8-a504-bf743b5c2919';

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
                        'due_datetime' => $taskData['deadline'] ? Carbon::parse($taskData['deadline'])->endOfDay() : null,
                    ]);

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

            // Create clarification questions as a comments or mindmap? Or just log them.
            // Let's create an initial post or comment on the workspace if possible, or add it to workspace description
            if ($request->has('clarification_questions') && is_array($request->clarification_questions)) {
                $questionsText = "\n\n**AI Clarification Questions to Client:**\n";
                foreach ($request->clarification_questions as $index => $question) {
                    $questionsText .= ($index + 1) . ". " . $question . "\n";
                }
                $workspace->description .= $questionsText;
                $workspace->save();
            }

            DB::commit();

            return redirect()->route('workspace.show', $workspace->id)->with('success', 'Proyek berhasil dibuat dari brief!');

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
}
