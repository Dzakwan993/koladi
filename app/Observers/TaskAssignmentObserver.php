<?php

namespace App\Observers;

use App\Models\TaskAssignment;
use Illuminate\Support\Facades\Log;

/**
 * TaskAssignmentObserver — Langkah 2 Verifikasi A3 (Blind Spot)
 *
 * TUJUAN VERIFIKASI:
 * Membuktikan secara empiris perbedaan antara:
 *   - Skenario X: Eloquent model delete  → observer TERPANGGIL  ✅
 *   - Skenario Y: Query builder delete   → observer TIDAK terpanggil ❌
 *
 * CATATAN PENTING:
 * TaskAssignment extends Pivot, bukan Model biasa.
 * Laravel mendukung Observer pada Pivot model selama model di-resolve
 * via Eloquent (bukan via query builder langsung).
 */
class TaskAssignmentObserver
{
    /**
     * Handle the TaskAssignment "created" event.
     */
    public function created(TaskAssignment $taskAssignment): void
    {
        Log::info('[TaskAssignmentObserver] created fired — Skenario X', [
            'task_id' => $taskAssignment->task_id,
            'user_id' => $taskAssignment->user_id,
            'method'  => 'Eloquent create/updateOrCreate',
        ]);
    }

    /**
     * Handle the TaskAssignment "deleted" event.
     *
     * Ini adalah method kunci untuk verifikasi A3.
     * Hanya akan terpanggil jika delete dilakukan via Eloquent model,
     * BUKAN via TaskAssignment::where(...)->delete() (query builder).
     */
    public function deleted(TaskAssignment $taskAssignment): void
    {
        Log::info('[TaskAssignmentObserver] deleted fired — Skenario X TERKONFIRMASI', [
            'task_id' => $taskAssignment->task_id,
            'user_id' => $taskAssignment->user_id,
            'method'  => 'Eloquent model delete — observer berjalan normal',
        ]);
    }
}
