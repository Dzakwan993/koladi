<?php

use App\Models\Workspace;
use App\Models\Task;
use App\Models\User;
use App\Models\TaskAssignment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

$scenarios = [
    'Small' => ['name' => 'Div. Desain', 'total' => 50, 'done' => 20, 'inprogress' => 18, 'todo' => 12],
    'Medium' => ['name' => 'HR Keuangan', 'total' => 200, 'done' => 80, 'inprogress' => 70, 'todo' => 50],
    'Large' => ['name' => 'MOU PT Xyz', 'total' => 500, 'done' => 200, 'inprogress' => 175, 'todo' => 125],
];

echo "Adjusting Tasks for Research Scenarios...\n";

foreach ($scenarios as $scenario => $info) {
    $workspace = Workspace::where('name', $info['name'])->first();
    if (!$workspace) {
        echo "[{$scenario}] Workspace '{$info['name']}' NOT FOUND!\n";
        continue;
    }

    echo "[{$scenario}] Processing {$workspace->name} (Target: {$info['total']} tasks)...\n";

    DB::beginTransaction();
    try {
        // Delete existing tasks to recreate perfectly
        $taskIds = Task::withTrashed()->where('workspace_id', $workspace->id)->pluck('id');
        
        // Delete assignments (if not cascaded)
        DB::table('task_assignments')->whereIn('task_id', $taskIds)->delete();
        
        // Delete tasks
        Task::withTrashed()->where('workspace_id', $workspace->id)->forceDelete();

        $members = $workspace->users()->pluck('users.id')->toArray();
        if (empty($members)) {
            echo "  WARNING: Workspace has no members!\n";
            $members = [1]; // fallback
        }

        $creatorId = $workspace->created_by ?? $members[0];
        
        $createTasks = function($count, $status, $isOverdue) use ($workspace, $creatorId) {
            $tasks = [];
            for ($i = 0; $i < $count; $i++) {
                $now = Carbon::now();
                
                if ($status === 'done') {
                    $start = $now->copy()->subDays(rand(10, 30));
                    $due = $now->copy()->subDays(rand(1, 10)); // Deadline in the past
                    $completedAt = $due->copy()->subDays(rand(1, 3)); // Completed before deadline
                } else {
                    $start = $now->copy()->subDays(rand(1, 10));
                    if ($isOverdue) {
                        $due = $now->copy()->subDays(rand(1, 14)); // Overdue
                    } else {
                        $due = $now->copy()->addYear()->addDays(rand(1, 30)); // 1 year in future
                    }
                    $completedAt = null;
                }

                $tasks[] = [
                    'id' => (string) Str::uuid(),
                    'workspace_id' => $workspace->id,
                    'created_by' => $creatorId,
                    'title' => "Task {$status} " . uniqid(),
                    'description' => "Description for research scenario.",
                    'status' => $status,
                    'priority' => ['low', 'medium', 'high', 'urgent'][rand(0, 3)],
                    'start_datetime' => $start,
                    'due_datetime' => $due,
                    'completed_at' => $completedAt,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            return $tasks;
        };

        // Done: 100% not technically overdue since they are completed
        $doneTasks = $createTasks($info['done'], 'done', false);
        
        // In Progress: 50% overdue, 50% future
        $ipOverdueCount = floor($info['inprogress'] / 2);
        $ipFutureCount = $info['inprogress'] - $ipOverdueCount;
        $ipTasksOverdue = $createTasks($ipOverdueCount, 'inprogress', true);
        $ipTasksFuture = $createTasks($ipFutureCount, 'inprogress', false);

        // Todo: 50% overdue, 50% future
        $todoOverdueCount = floor($info['todo'] / 2);
        $todoFutureCount = $info['todo'] - $todoOverdueCount;
        $todoTasksOverdue = $createTasks($todoOverdueCount, 'todo', true);
        $todoTasksFuture = $createTasks($todoFutureCount, 'todo', false);

        $allTasks = array_merge($doneTasks, $ipTasksOverdue, $ipTasksFuture, $todoTasksOverdue, $todoTasksFuture);

        // Insert tasks in chunks
        foreach (array_chunk($allTasks, 100) as $chunk) {
            Task::insert($chunk);
        }

        // Assign users to tasks safely via Eloquent relationships
        $createdTasks = Task::where('workspace_id', $workspace->id)->get();
        foreach ($createdTasks as $task) {
            // Randomly assign 1 to 3 members
            $assignCount = rand(1, min(3, count($members)));
            shuffle($members);
            $assigned = array_slice($members, 0, $assignCount);
            
            $task->assignedUsers()->sync($assigned);
        }

        DB::commit();
        echo "  Successfully recreated {$info['total']} tasks!\n";

    } catch (\Exception $e) {
        DB::rollBack();
        echo "  FAILED: " . $e->getMessage() . "\n";
    }
}
echo "Done.\n";
