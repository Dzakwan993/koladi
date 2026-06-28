<?php

use App\Models\Workspace;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

$workspaces = [
    'Small' => ['name' => 'Div. Desain', 'target_tasks' => 50, 'target_members' => 5],
    'Medium' => ['name' => 'HR Keuangan', 'target_tasks' => 200, 'target_members' => 15],
    'Large' => ['name' => 'MOU PT Xyz', 'target_tasks' => 500, 'target_members' => 30],
];

echo "Checking workspaces...\n";

foreach ($workspaces as $scenario => $info) {
    // Find workspace by a fuzzy match or exact match
    $workspace = Workspace::where('name', 'like', '%' . $info['name'] . '%')->first();
    
    if (!$workspace) {
        echo "[{$scenario}] Workspace '{$info['name']}' NOT FOUND!\n";
        continue;
    }

    $memberCount = $workspace->users()->count();
    $taskCount = Task::where('workspace_id', $workspace->id)->count();

    $todoCount = Task::where('workspace_id', $workspace->id)->where('status', 'todo')->count();
    $inProgressCount = Task::where('workspace_id', $workspace->id)->where('status', 'inprogress')->count();
    $doneCount = Task::where('workspace_id', $workspace->id)->where('status', 'done')->count();

    $overdueCount = Task::where('workspace_id', $workspace->id)
        ->where('status', '!=', 'done')
        ->where('due_datetime', '<', now())
        ->count();

    echo "[{$scenario}] Workspace: {$workspace->name} (ID: {$workspace->id})\n";
    echo "  Members: {$memberCount} / Target: {$info['target_members']}\n";
    echo "  Tasks: {$taskCount} / Target: {$info['target_tasks']}\n";
    echo "  Status Distribution: \n";
    echo "    - Done (Target 40%): {$doneCount} / " . ($taskCount > 0 ? round(($doneCount/$taskCount)*100, 1) : 0) . "%\n";
    echo "    - In Progress (Target 35%): {$inProgressCount} / " . ($taskCount > 0 ? round(($inProgressCount/$taskCount)*100, 1) : 0) . "%\n";
    echo "    - Todo (Target 25%): {$todoCount} / " . ($taskCount > 0 ? round(($todoCount/$taskCount)*100, 1) : 0) . "%\n";
    echo "  Overdue Tasks (Not Done): {$overdueCount}\n\n";
}
