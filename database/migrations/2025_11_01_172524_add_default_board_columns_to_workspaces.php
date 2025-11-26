<?php

use App\Models\Workspace;
use App\Models\BoardColumn;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        // Ambil semua workspace yang ada
        $workspaces = Workspace::all();

        foreach ($workspaces as $workspace) {
            // Cek apakah workspace sudah memiliki kolom default
            $existingColumns = BoardColumn::where('workspace_id', $workspace->id)->count();

            if ($existingColumns === 0) {
                // Buat 4 kolom default
                $defaultColumns = [
                    ['name' => 'To Do List', 'position' => 1],
                    ['name' => 'Dikerjakan', 'position' => 2],
                    ['name' => 'Selesai', 'position' => 3],
                    ['name' => 'Batal', 'position' => 4],
                ];

                foreach ($defaultColumns as $column) {
                    BoardColumn::create([
                        'id' => Str::uuid()->toString(),
                        'workspace_id' => $workspace->id,
                        'name' => $column['name'],
                        'position' => $column['position'],
                        'created_by' => $workspace->created_by,
                    ]);
                }
            }
        }
    }

    public function down()
    {
        // Tidak perlu rollback untuk data seeding
    }
};
