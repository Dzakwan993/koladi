<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Workspace;
use App\Models\User;
use App\Models\Task;
use App\Models\BoardColumn;
use App\Models\Role;
use App\Models\UserCompany;
use App\Models\UserWorkspace;
use Illuminate\Support\Str;

class ResearchDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting Research Data Seeder...');

        // 1. Dapatkan atau Buat Role Member
        $memberRole = Role::firstOrCreate(
            ['name' => 'Member'],
            ['id' => Str::uuid()->toString()]
        );
        
        $adminRole = Role::firstOrCreate(
            ['name' => 'Admin'],
            ['id' => Str::uuid()->toString()]
        );

        // 2. Buat atau Temukan Perusahaan
        $company = Company::firstOrCreate(
            ['name' => 'Bismillah'],
            ['id' => Str::uuid()->toString()]
        );

        // 3. Buat satu User Utama sebagai Admin Perusahaan
        $mainAdmin = User::firstOrCreate(
            ['email' => 'admin.bismillah@example.com'],
            [
                'id' => Str::uuid()->toString(),
                'full_name' => 'Main Admin PT Bismillah',
                'password' => bcrypt('password'),
            ]
        );

        // Pastikan Admin terhubung ke Company
        UserCompany::firstOrCreate([
            'user_id' => $mainAdmin->id,
            'company_id' => $company->id,
        ], [
            'id' => Str::uuid()->toString(),
            'roles_id' => $adminRole->id,
            'status_active' => true
        ]);

        // 4. Definisi Skenario
        $scenarios = [
            [
                'name' => 'Div. Desain', // Small
                'tasks_count' => 50,
                'members_count' => 5,
                'assignees_per_task' => 2
            ],
            // [
            //     'name' => 'HR Keuangan', // Medium
            //     'tasks_count' => 200,
            //     'members_count' => 15,
            //     'assignees_per_task' => 3
            // ],
            // [
            //     'name' => 'MOU PT Xyz', // Large
            //     'tasks_count' => 500,
            //     'members_count' => 30,
            //     'assignees_per_task' => 3
            // ]
        ];

        foreach ($scenarios as $scenario) {
            $this->command->info("--- Seeding Workspace: {$scenario['name']} ---");

            // A. Buat Workspace
            $workspace = Workspace::firstOrCreate(
                [
                    'company_id' => $company->id,
                    'name' => $scenario['name']
                ],
                ['id' => Str::uuid()->toString()]
            );

            // B. Buat Anggota (Users)
            $users = User::factory()->count($scenario['members_count'])->create();
            
            foreach ($users as $user) {
                // Link user ke company
                UserCompany::firstOrCreate([
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                ], [
                    'id' => Str::uuid()->toString(),
                    'roles_id' => $memberRole->id,
                    'status_active' => true
                ]);

                // Link user ke workspace
                UserWorkspace::firstOrCreate([
                    'user_id' => $user->id,
                    'workspace_id' => $workspace->id,
                ], [
                    'id' => Str::uuid()->toString(),
                    'roles_id' => $memberRole->id,
                    'status_active' => true
                ]);
            }

            // Pastikan Main Admin punya akses ke semua workspace
            UserWorkspace::firstOrCreate([
                'user_id' => $mainAdmin->id,
                'workspace_id' => $workspace->id,
            ], [
                'id' => Str::uuid()->toString(),
                'roles_id' => $adminRole->id,
                'status_active' => true
            ]);

            // C. Board Columns
            $columns = [
                'To Do List' => 'todo',
                'Dikerjakan' => 'inprogress',
                'Selesai' => 'done'
            ];
            $columnModels = [];
            foreach ($columns as $colName => $status) {
                $columnModels[$status] = BoardColumn::firstOrCreate([
                    'workspace_id' => $workspace->id,
                    'name' => $colName
                ], [
                    'id' => Str::uuid()->toString(),
                    'position' => array_search($colName, array_keys($columns))
                ]);
            }

            // D. Tasks
            $this->command->info("Creating {$scenario['tasks_count']} tasks...");
            for ($i = 0; $i < $scenario['tasks_count']; $i++) {
                $task = Task::factory()->create([
                    'workspace_id' => $workspace->id,
                    'created_by' => $mainAdmin->id,
                    'board_column_id' => function (array $attributes) use ($columnModels) {
                        return $columnModels[$attributes['status']]->id;
                    }
                ]);

                $assignees = $users->random(min($scenario['assignees_per_task'], $users->count()));
                foreach ($assignees as $assignee) {
                    $task->assignUser($assignee->id);
                }
            }
        }

        $this->command->info('Research Data Seeding Completed!');
    }
}
