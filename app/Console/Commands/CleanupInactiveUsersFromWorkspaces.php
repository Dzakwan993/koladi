<?php

namespace App\Console\Commands;

use App\Models\Workspace;
use App\Models\UserCompany;
use App\Models\UserWorkspace;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupInactiveUsersFromWorkspaces extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workspace:cleanup-inactive-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove inactive users from all workspaces';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Starting cleanup of inactive users from workspaces...');

        DB::beginTransaction();
        
        try {
            $totalRemoved = 0;

            // Ambil semua workspace
            $workspaces = Workspace::all();

            foreach ($workspaces as $workspace) {
                $this->info("Processing workspace: {$workspace->name} (ID: {$workspace->id})");

                // Ambil user workspace yang user-nya nonaktif di company
                $inactiveUserWorkspaces = UserWorkspace::where('workspace_id', $workspace->id)
                    ->whereHas('user.userCompanies', function($q) use ($workspace) {
                        $q->where('company_id', $workspace->company_id)
                          ->where('status_active', false);
                    })
                    ->get();

                $count = $inactiveUserWorkspaces->count();
                
                if ($count > 0) {
                    foreach ($inactiveUserWorkspaces as $uw) {
                        $this->line("  → Removing user {$uw->user->full_name} (ID: {$uw->user_id})");
                        $uw->delete();
                    }
                    
                    $totalRemoved += $count;
                    $this->info("  ✅ Removed {$count} inactive user(s) from {$workspace->name}");
                }
            }

            DB::commit();

            $this->info("✅ Cleanup completed! Total removed: {$totalRemoved} user-workspace entries");
            
            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error during cleanup: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}