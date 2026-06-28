<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\Workspace;
use App\Models\UserWorkspace;
use Illuminate\Support\Facades\Log;

/**
 * Artisan Command untuk verifikasi observer pre-eksperimen.
 *
 * Jalankan dengan:
 *   docker compose -f docker-compose.local.yml exec app php artisan dss:verify-observers
 *
 * Atau jika app sudah running lokal:
 *   php artisan dss:verify-observers
 */
class VerifyObserversCommand extends Command
{
    protected $signature   = 'dss:verify-observers {--clean : Hapus data test setelah selesai}';
    protected $description = 'Verifikasi TaskObserver (Langkah 1) dan A3 blind spot (Langkah 2) sebelum eksperimen utama';

    public function handle(): int
    {
        $this->newLine();
        $this->line('<fg=cyan>╔══════════════════════════════════════════════════╗</>');
        $this->line('<fg=cyan>║   PRE-EKSPERIMEN: Verifikasi Observer DSS        ║</>');
        $this->line('<fg=cyan>╚══════════════════════════════════════════════════╝</>');
        $this->newLine();

        // ── Cari workspace dengan tasks DAN member aktif ──────────────
        $workspace = Workspace::whereHas('tasks')
            ->whereHas('activeMembers')
            ->first();

        if (!$workspace) {
            // Fallback: ambil semua workspace, cari yang punya member aktif
            $workspace = Workspace::all()->first(function ($w) {
                return UserWorkspace::where('workspace_id', $w->id)
                    ->where('status_active', true)->exists()
                    && Task::where('workspace_id', $w->id)->exists();
            });
        }

        if (!$workspace) {
            $this->error('Tidak ada workspace dengan tasks dan member aktif.');
            $this->line('Pastikan company sudah aktif (status=trial) dan workspace memiliki data.');
            return self::FAILURE;
        }

        $userWorkspace = UserWorkspace::where('workspace_id', $workspace->id)
            ->where('status_active', true)
            ->first();

        $memberId = $userWorkspace->user_id;

        $this->line("Workspace : <fg=yellow>{$workspace->name}</> ({$workspace->id})");
        $this->line("Member ID : <fg=yellow>{$memberId}</>");
        $this->newLine();

        // ════════════════════════════════════════════════════════
        // LANGKAH 1 — Verifikasi TaskObserver
        // ════════════════════════════════════════════════════════
        $this->line('<fg=green>━━━━ LANGKAH 1: Verifikasi TaskObserver ━━━━</>');
        $this->newLine();

        $defaultColumn = \App\Models\BoardColumn::where('workspace_id', $workspace->id)
            ->where('name', 'like', '%To Do%')
            ->first();

        // Test: created
        $this->line('▶ Test <fg=yellow>created</> event...');
        $task = Task::create([
            'workspace_id'    => $workspace->id,
            'board_column_id' => $defaultColumn?->id,
            'created_by'      => $memberId,
            'title'           => '[OBSERVER-TEST] Verifikasi ' . now()->format('H:i:s'),
            'description'     => 'Task dummy verifikasi observer pre-eksperimen',
            'status'          => 'todo',
            'start_datetime'  => now(),
            'due_datetime'    => now()->addDays(3),
        ]);
        $this->info("   Task created: {$task->id}");
        $this->line('   Ekspektasi log: <fg=cyan>[TaskObserver] created fired</>');

        // Test: updated
        $this->newLine();
        $this->line('▶ Test <fg=yellow>updated</> event...');
        $task->status = 'inprogress';
        $task->save();
        $this->info('   Task updated: status → inprogress');
        $this->line('   Ekspektasi log: <fg=cyan>[TaskObserver] updated fired, dirty_fields: [status]</>');

        // Test: deleted
        $this->newLine();
        $this->line('▶ Test <fg=yellow>deleted</> event...');
        $taskId = $task->id;
        $task->delete();
        $this->info("   Task deleted: {$taskId}");
        $this->line('   Ekspektasi log: <fg=cyan>[TaskObserver] deleted fired</>');

        $this->newLine();
        $this->line('<fg=green>Langkah 1 selesai. Verifikasi log:</>');
        $this->line('  <fg=gray>grep "TaskObserver" storage/logs/laravel.log | tail -10</>');
        $this->newLine();

        // ════════════════════════════════════════════════════════
        // LANGKAH 2 — Verifikasi A3: Skenario X vs Y
        // ════════════════════════════════════════════════════════
        $this->line('<fg=magenta>━━━━ LANGKAH 2: Verifikasi A3 Blind Spot ━━━━</>');
        $this->newLine();

        // Gunakan task yang berbeda agar tidak konflik
        $testTask = Task::where('workspace_id', $workspace->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$testTask) {
            $this->warn('Tidak ada task untuk uji assignment. Skip Langkah 2.');
            return self::SUCCESS;
        }

        $this->line("Task untuk uji: <fg=yellow>{$testTask->title}</> ({$testTask->id})");
        $this->newLine();

        // ── Skenario X: Eloquent delete → observer HARUS terpanggil ──
        $this->line('▶ <fg=green>SKENARIO X</> — Eloquent model delete (.get()->each->delete())');
        $this->line('  Ekspektasi: [TaskAssignmentObserver] deleted fired ✅');

        // Buat assignment untuk ditest
        $existing = TaskAssignment::where('task_id', $testTask->id)
            ->where('user_id', $memberId)->first();
        if (!$existing) {
            TaskAssignment::create([
                'task_id'     => $testTask->id,
                'user_id'     => $memberId,
                'assigned_at' => now(),
            ]);
        }

        // Eloquent delete — cara yang BENAR (solusi A3)
        TaskAssignment::where('task_id', $testTask->id)
            ->where('user_id', $memberId)
            ->get()
            ->each(fn($a) => $a->delete()); // Setiap model di-delete satu per satu → observer terpanggil

        $this->info('  Skenario X dieksekusi — cek log untuk memastikan observer terpanggil');
        $this->newLine();

        // ── Skenario Y: Query builder delete → observer TIDAK terpanggil ──
        $this->line('▶ <fg=red>SKENARIO Y</> — Query builder delete (cara manageTaskAssignments() saat ini)');
        $this->line('  Ekspektasi: TIDAK ADA log dari TaskAssignmentObserver ❌');

        // Buat ulang assignment
        TaskAssignment::create([
            'task_id'     => $testTask->id,
            'user_id'     => $memberId,
            'assigned_at' => now(),
        ]);

        // Query builder delete — cara yang ADA di kode sekarang (TaskController.php L447-449)
        TaskAssignment::where('task_id', $testTask->id)
            ->where('user_id', $memberId)
            ->delete(); // Bypass observer!

        $this->info('  Skenario Y dieksekusi — observer TIDAK dipanggil (blind spot terbukti)');
        $this->newLine();

        // ── Ringkasan ──────────────────────────────────────────
        $this->line('<fg=cyan>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</>');
        $this->line('<fg=cyan>CARA VERIFIKASI HASIL</>');
        $this->line('<fg=cyan>━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━</>');
        $this->newLine();
        $this->line('Jalankan perintah berikut setelah command ini selesai:');
        $this->newLine();
        $this->line('  <fg=yellow>grep "TaskObserver\|TaskAssignment" storage/logs/laravel.log | tail -20</>');
        $this->newLine();
        $this->line('HASIL YANG DIHARAPKAN:');
        $this->line('  ✅  3 baris: [TaskObserver] created / updated / deleted fired');
        $this->line('  ✅  1 baris: [TaskAssignmentObserver] created fired (dari Skenario X setup)');
        $this->line('  ✅  1 baris: [TaskAssignmentObserver] deleted fired — Skenario X TERKONFIRMASI');
        $this->line('  ❌  0 baris untuk Skenario Y (TIDAK ada log setelah query builder delete)');
        $this->newLine();
        $this->line('Jika hasil sesuai:');
        $this->line('  → <fg=green>TaskObserver berfungsi normal</> (Langkah 1 ✅)');
        $this->line('  → <fg=green>A3 TERBUKTI EMPIRIS</> — manageTaskAssignments() harus diubah');
        $this->line('  → <fg=green>Tidak perlu fallback manual</> jika query builder diperbaiki ke Eloquent');
        $this->newLine();

        return self::SUCCESS;
    }
}
