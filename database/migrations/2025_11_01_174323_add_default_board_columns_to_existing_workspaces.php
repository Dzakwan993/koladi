<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Workspace;
use App\Models\BoardColumn;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up()
    {
        // Ambil semua workspace yang ada
        $workspaces = Workspace::all();
        
        foreach ($workspaces as $workspace) {
            // Gunakan method yang sudah kita buat di model
            $workspace->createDefaultColumnsIfNotExists();
        }
    }

    public function down()
    {
        // Tidak perlu rollback untuk data seeding
    }
};