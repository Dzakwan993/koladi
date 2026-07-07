<?php

namespace App\Observers;

use App\Models\Task;
use App\Services\DSS\DSSService;
use Illuminate\Support\Facades\Log;

/**
 * TaskObserver — Event-Driven Cache Invalidation
 *
 * Menginvalidasi cache DSS Redis pada event created, updated, deleted
 * sehingga data DSS selalu fresh setelah ada perubahan pada Task.
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

        $this->invalidateWorkspaceCache($task->workspace_id);
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
            'dirty_fields' => array_keys($task->getDirty()),
        ]);

        $this->invalidateWorkspaceCache($task->workspace_id);
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

        $this->invalidateWorkspaceCache($task->workspace_id);
    }

    /**
     * Invalidasi cache DSS Redis untuk workspace yang terpengaruh.
     */
    private function invalidateWorkspaceCache(string $workspaceId): void
    {
        try {
            app(DSSService::class)->invalidateCacheForWorkspace($workspaceId);
        } catch (\Throwable $e) {
            Log::warning('[TaskObserver] Cache invalidation failed', [
                'workspace_id' => $workspaceId,
                'error'        => $e->getMessage(),
            ]);
        }
    }
}
