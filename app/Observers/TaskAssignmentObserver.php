<?php

namespace App\Observers;

use App\Models\TaskAssignment;
use App\Services\DSS\DSSService;
use Illuminate\Support\Facades\Log;

/**
 * TaskAssignmentObserver — Event-Driven Cache Invalidation
 *
 * TUJUAN:
 * Menginvalidasi cache DSS Redis ketika terjadi perubahan assignment anggota
 * pada sebuah task. Perubahan assignment memengaruhi workload distribution,
 * sehingga cache harus dihapus agar data DSS tetap fresh.
 *
 * CATATAN PENTING:
 * Observer ini hanya terpanggil jika delete/create dilakukan via Eloquent model,
 * BUKAN via query builder langsung (TaskAssignment::where(...)->delete()).
 */
class TaskAssignmentObserver
{
    /**
     * Handle the TaskAssignment "created" event.
     */
    public function created(TaskAssignment $taskAssignment): void
    {
        Log::info('[TaskAssignmentObserver] created fired', [
            'task_id' => $taskAssignment->task_id,
            'user_id' => $taskAssignment->user_id,
            'method'  => 'Eloquent create',
        ]);

        $this->invalidateCacheForTask($taskAssignment->task_id);
    }

    /**
     * Handle the TaskAssignment "deleted" event.
     */
    public function deleted(TaskAssignment $taskAssignment): void
    {
        Log::info('[TaskAssignmentObserver] deleted fired', [
            'task_id' => $taskAssignment->task_id,
            'user_id' => $taskAssignment->user_id,
            'method'  => 'Eloquent model delete',
        ]);

        $this->invalidateCacheForTask($taskAssignment->task_id);
    }

    /**
     * Invalidasi cache DSS berdasarkan task_id → workspace_id.
     */
    private function invalidateCacheForTask(string $taskId): void
    {
        try {
            // Ambil workspace_id dari task yang terkait
            $task = \App\Models\Task::select('workspace_id')->find($taskId);
            if (!$task) {
                Log::warning('[TaskAssignmentObserver] Task not found for invalidation', ['task_id' => $taskId]);
                return;
            }

            app(DSSService::class)->invalidateCacheForWorkspace($task->workspace_id);
        } catch (\Throwable $e) {
            Log::warning('[TaskAssignmentObserver] Cache invalidation failed', [
                'task_id' => $taskId,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
