<?php

use App\Models\Workspace;
use App\Models\Task;
use App\Models\BoardColumn;

$scenarios = ['Div. Desain', 'HR Keuangan', 'MOU PT Xyz'];

echo "Fixing Board Columns...\n";

foreach ($scenarios as $name) {
    $workspace = Workspace::where('name', $name)->first();
    if (!$workspace) continue;

    $columns = BoardColumn::where('workspace_id', $workspace->id)->get();
    
    // Fuzzy matching for column names
    $todoCol = $columns->first(fn($c) => stripos($c->name, 'to do') !== false || stripos($c->name, 'todo') !== false);
    $ipCol = $columns->first(fn($c) => stripos($c->name, 'kerja') !== false || stripos($c->name, 'progress') !== false);
    $doneCol = $columns->first(fn($c) => stripos($c->name, 'selesai') !== false || stripos($c->name, 'done') !== false);

    if ($todoCol) {
        Task::where('workspace_id', $workspace->id)->where('status', 'todo')->update(['board_column_id' => $todoCol->id]);
    }
    if ($ipCol) {
        Task::where('workspace_id', $workspace->id)->where('status', 'inprogress')->update(['board_column_id' => $ipCol->id]);
    }
    if ($doneCol) {
        Task::where('workspace_id', $workspace->id)->where('status', 'done')->update(['board_column_id' => $doneCol->id]);
    }

    echo "Fixed columns for: {$name}\n";
}
echo "Done.\n";
