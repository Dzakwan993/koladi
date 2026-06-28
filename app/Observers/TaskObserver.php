<?php

namespace App\Observers;

use App\Models\Task;
use Illuminate\Support\Facades\Log;

/**
 * TaskObserver — Langkah 1 Verifikasi Pre-Eksperimen
 *
 * Saat ini hanya Log::info untuk memverifikasi bahwa lifecycle hooks
 * Observer berjalan normal pada model Task.
 *
 * Setelah verifikasi berhasil, method ini akan diisi dengan:
 *   Cache::store('redis')->forget("dss:{$task->workspace_id}:*");
 */
class TaskObserver
{
    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        Log::info('[TaskObserver] created fired', [
            'task_id'      => $task->id,
            'title'        => $task->title,
            'workspace_id' => $task->workspace_id,
            'status'       => $task->status,
        ]);
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        Log::info('[TaskObserver] updated fired', [
            'task_id'      => $task->id,
            'title'        => $task->title,
            'workspace_id' => $task->workspace_id,
            'dirty_fields' => array_keys($task->getDirty()), // Kolom yang berubah
        ]);
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
        Log::info('[TaskObserver] deleted fired', [
            'task_id'      => $task->id,
            'title'        => $task->title,
            'workspace_id' => $task->workspace_id,
        ]);
    }
}
