<?php
try {
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    
    $hasColumn = \Illuminate\Support\Facades\Schema::hasColumn('labels', 'workspace_id');
    $actions = [];
    
    if (!$hasColumn) {
        // 1. Add workspace_id column as nullable UUID
        \Illuminate\Support\Facades\Schema::table('labels', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->uuid('workspace_id')->nullable();
        });
        $actions[] = 'Added workspace_id column as nullable';
        
        // 2. Set default workspace for existing labels to avoid deletion (if any workspace exists)
        $firstWorkspace = \Illuminate\Support\Facades\DB::table('workspaces')->first();
        if ($firstWorkspace) {
            \Illuminate\Support\Facades\DB::table('labels')->whereNull('workspace_id')->update([
                'workspace_id' => $firstWorkspace->id
            ]);
            $actions[] = 'Assigned existing labels to workspace: ' . $firstWorkspace->id;
        } else {
            // Delete if no workspace exists to avoid violation
            \Illuminate\Support\Facades\DB::table('labels')->whereNull('workspace_id')->delete();
            $actions[] = 'Deleted orphaned labels (no workspace found)';
        }
        
        // 3. Make the column NOT NULL
        \Illuminate\Support\Facades\Schema::table('labels', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->uuid('workspace_id')->nullable(false)->change();
        });
        $actions[] = 'Made workspace_id NOT NULL';
        
        // 4. Add foreign key constraint
        \Illuminate\Support\Facades\Schema::table('labels', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->foreign('workspace_id')
                  ->references('id')
                  ->on('workspaces')
                  ->onDelete('cascade');
        });
        $actions[] = 'Added foreign key constraint on workspace_id';
    } else {
        $actions[] = 'Column workspace_id already exists';
    }
    
    // Also try to reset OPCache if enabled
    if (function_exists('opcache_reset')) {
        opcache_reset();
        $actions[] = 'OPCache reset successful';
    }
    
    echo json_encode([
        'success' => true,
        'actions' => $actions,
        'columns' => \Illuminate\Support\Facades\Schema::getColumnListing('labels')
    ]);
} catch (\Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
