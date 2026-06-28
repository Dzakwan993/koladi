<?php
/**
 * LANGKAH 2 — Verifikasi A3: TaskAssignment Observer Blind Spot
 *
 * Jalankan via: php artisan tinker --execute="require 'scripts/verify_langkah2_a3_blindspot.php';"
 *
 * Setelah dijalankan, cek log:
 *   tail -n 60 storage/logs/laravel.log | grep "TaskAssignment"
 *
 * EKSPEKTASI HASIL:
 *   Skenario X (Eloquent delete):
 *     → [TaskAssignmentObserver] deleted fired — Skenario X TERKONFIRMASI  ✅
 *
 *   Skenario Y (query builder delete — seperti manageTaskAssignments() saat ini):
 *     → TIDAK ADA log dari TaskAssignmentObserver                           ❌
 *     → Ini MEMBUKTIKAN masalah A3
 */

use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\Workspace;
use App\Models\UserWorkspace;

echo "\n=====================================================\n";
echo "LANGKAH 2 — Verifikasi A3: Observer Blind Spot\n";
echo "=====================================================\n\n";

// ── Setup: Ambil task dan member yang valid ──────────────
$workspace = Workspace::whereHas('tasks')->first();
if (!$workspace) {
    echo "❌ Tidak ada workspace dengan task. Jalankan Langkah 1 dulu atau buat data.\n";
    return;
}

$task = Task::where('workspace_id', $workspace->id)->first();
$member = UserWorkspace::where('workspace_id', $workspace->id)
    ->where('status_active', true)
    ->with('user')
    ->first()
    ?->user;

if (!$task || !$member) {
    echo "❌ Tidak ada task atau member di workspace '{$workspace->name}'.\n";
    return;
}

echo "Workspace : {$workspace->name}\n";
echo "Task      : {$task->title} ({$task->id})\n";
echo "Member    : {$member->name} ({$member->id})\n\n";

// ── Pastikan ada assignment untuk ditest ─────────────────
// Buat assignment terlebih dahulu jika belum ada
$existing = TaskAssignment::where('task_id', $task->id)
    ->where('user_id', $member->id)
    ->first();

if (!$existing) {
    TaskAssignment::create([
        'task_id'     => $task->id,
        'user_id'     => $member->id,
        'assigned_at' => now(),
    ]);
    echo "Assignment dummy dibuat untuk testing.\n\n";
} else {
    echo "Assignment sudah ada, akan digunakan untuk testing.\n\n";
}

// ═══════════════════════════════════════════════════════════
// SKENARIO X — Eloquent model delete
// Observer HARUS terpanggil
// ═══════════════════════════════════════════════════════════
echo "─────────────────────────────────────────────────\n";
echo "SKENARIO X: Eloquent model delete (.each->delete())\n";
echo "─────────────────────────────────────────────────\n";
echo "Ekspektasi: [TaskAssignmentObserver] deleted fired\n\n";

// Pastikan ada assignment
if (!TaskAssignment::where('task_id', $task->id)->where('user_id', $member->id)->exists()) {
    TaskAssignment::create(['task_id' => $task->id, 'user_id' => $member->id, 'assigned_at' => now()]);
}

// Cara yang BENAR (solusi yang diusulkan di konteksPenelitian.md)
TaskAssignment::where('task_id', $task->id)
    ->where('user_id', $member->id)
    ->get()           // Load sebagai Collection Eloquent model
    ->each(function ($assignment) {
        $assignment->delete(); // Delete per model → MEMICU observer
    });

echo "▶ Eloquent delete dieksekusi.\n";
echo "▶ Tunggu 1 detik lalu cek log...\n\n";
sleep(1);

// ═══════════════════════════════════════════════════════════
// SKENARIO Y — Query builder delete (cara sekarang di kode)
// Observer TIDAK TERPANGGIL → ini yang jadi masalah A3
// ═══════════════════════════════════════════════════════════
echo "─────────────────────────────────────────────────\n";
echo "SKENARIO Y: Query builder delete (cara manageTaskAssignments() saat ini)\n";
echo "─────────────────────────────────────────────────\n";
echo "Ekspektasi: TIDAK ADA log dari TaskAssignmentObserver\n\n";

// Buat ulang assignment untuk di-delete dengan query builder
TaskAssignment::create(['task_id' => $task->id, 'user_id' => $member->id, 'assigned_at' => now()]);

// Cara yang SALAH (dari TaskController.php L447-449)
// Ini yang ada di manageTaskAssignments() sekarang:
TaskAssignment::where('task_id', $task->id)
    ->where('user_id', $member->id)
    ->delete(); // ← Query builder delete — BYPASS observer!

echo "▶ Query builder delete dieksekusi.\n";
echo "▶ Observer TIDAK akan terpanggil.\n\n";

// ── Instruksi verifikasi ─────────────────────────────────
echo "=====================================================\n";
echo "CARA VERIFIKASI HASIL:\n\n";
echo "Jalankan perintah ini di terminal terpisah:\n";
echo "  tail -n 60 storage/logs/laravel.log | grep 'TaskAssignment'\n\n";
echo "HASIL YANG DIHARAPKAN:\n";
echo "  ✅ Muncul 1x: '[TaskAssignmentObserver] deleted fired — Skenario X'\n";
echo "  ❌ TIDAK muncul log untuk Skenario Y\n\n";
echo "Jika hasil sesuai ekspektasi:\n";
echo "  → A3 TERBUKTI EMPIRIS\n";
echo "  → manageTaskAssignments() perlu diubah ke Eloquent delete\n";
echo "=====================================================\n";
