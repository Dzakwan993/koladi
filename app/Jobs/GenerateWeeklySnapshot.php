<?php

namespace App\Jobs;

use App\Services\DSS\DSSService;
use App\Models\Workspace;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GenerateWeeklySnapshot implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $workspaceId;

    /**
     * Create a new job instance.
     */
    public function __construct($workspaceId = null)
    {
        $this->workspaceId = $workspaceId;
    }

    /**
     * Execute the job.
     */
    public function handle(DSSService $dssService)
    {
        Log::info('=== GenerateWeeklySnapshot Job START ===');

        // Get period (last week)
        $now = Carbon::now();
        $periodStart = $now->copy()->subWeek()->startOfWeek()->toDateString();
        $periodEnd = $now->copy()->subWeek()->endOfWeek()->toDateString();

        Log::info('Period:', [
            'start' => $periodStart,
            'end' => $periodEnd
        ]);

        // If specific workspace
        if ($this->workspaceId) {
            $this->generateForWorkspace($dssService, $this->workspaceId, $periodStart, $periodEnd);
            return;
        }

        // Generate for all workspaces
        $workspaces = Workspace::all();
        
        Log::info("Generating snapshots for {$workspaces->count()} workspaces");

        foreach ($workspaces as $workspace) {
            try {
                $this->generateForWorkspace($dssService, $workspace->id, $periodStart, $periodEnd);
            } catch (\Exception $e) {
                Log::error("Failed to generate snapshot for workspace {$workspace->id}: " . $e->getMessage());
            }
        }

        Log::info('=== GenerateWeeklySnapshot Job COMPLETE ===');
    }

    /**
     * Generate snapshot for single workspace
     */
    private function generateForWorkspace(DSSService $dssService, $workspaceId, $periodStart, $periodEnd)
    {
        Log::info("Generating snapshot for workspace: $workspaceId");

        $dssService->getWorkspaceSuggestions(
            $workspaceId,
            $periodStart,
            $periodEnd,
            true // Force recalculate
        );

        Log::info("Snapshot generated successfully for workspace: $workspaceId");
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error('GenerateWeeklySnapshot Job Failed: ' . $exception->getMessage());
    }
}