<?php
/**
 * LANGKAH 1 — Verifikasi TaskObserver lifecycle hooks
 *
 * Jalankan via: php artisan tinker --execute="require 'scripts/verify_langkah1_task_observer.php';"
 *
 * Setelah dijalankan, cek log di:
 *   tail -n 50 storage/logs/laravel.log | grep "TaskObserver"
 *
 * Ekspektasi:
 *   [TaskObserver] created fired  → saat Task::create()
 *   [TaskObserver] updated fired  → saat $task->save()
 *   [TaskObserver] deleted fired  → saat $task->delete()
 */

use App\Models\Task;
use App\Models\Workspace;

echo "\n======================================\n";
echo "LANGKAH 1 — Verifikasi TaskObserver\n";
echo "======================================\n\n";

// Ambil workspace pertama yang ada
$workspace = Workspace::first();
if (!$workspace) {
    echo "❌ Tidak ada workspace di database. Buat workspace dulu.\n";
    return;
}

// Ambil board column default untuk workspace ini
$defaultColumn = \App\Models\BoardColumn::where('workspace_id', $workspace->id)
    ->where('name', 'like', '%To Do%')
    ->first();

echo "Workspace: {$workspace->name} ({$workspace->id})\n";
echo "Board Column: " . ($defaultColumn?->name ?? 'TIDAK DITEMUKAN') . "\n\n";

// ────────────────────────────────────────────────
// Test 1: created event
// ────────────────────────────────────────────────
echo "▶ Test created: membuat task dummy...\n";
$task = Task::create([
    'workspace_id'    => $workspace->id,
    'board_column_id' => $defaultColumn?->id,
    'created_by'      => \App\Models\User::first()->id,
    'title'           => '[OBSERVER-TEST] Task Verifikasi - ' . now()->format('H:i:s'),
    'description'     => 'Task dummy untuk verifikasi observer',
    'status'          => 'todo',
    'start_datetime'  => now(),
    'due_datetime'    => now()->addDays(3),
]);
echo "   Task dibuat: {$task->id}\n";
echo "   → Cek log untuk: [TaskObserver] created fired\n\n";

// ────────────────────────────────────────────────
// Test 2: updated event
// ────────────────────────────────────────────────
echo "▶ Test updated: mengubah status task...\n";
$task->status = 'inprogress';
$task->save();
echo "   Task diupdate: status → inprogress\n";
echo "   → Cek log untuk: [TaskObserver] updated fired, dirty_fields: [status]\n\n";

// ────────────────────────────────────────────────
// Test 3: deleted event
// ────────────────────────────────────────────────
echo "▶ Test deleted: menghapus task...\n";
$taskId = $task->id;
$task->delete(); // Soft delete (karena Task pakai SoftDeletes)
echo "   Task dihapus: {$taskId}\n";
echo "   → Cek log untuk: [TaskObserver] deleted fired\n\n";

echo "======================================\n";
echo "Selesai. Jalankan perintah berikut:\n";
echo "  tail -n 30 storage/logs/laravel.log | grep TaskObserver\n";
echo "======================================\n";
