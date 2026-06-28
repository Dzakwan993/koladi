<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use App\Models\BoardColumn;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        // Logika Distribusi Status: 40% done, 35% in progress, 25% todo
        $rand = rand(1, 100);
        if ($rand <= 40) {
            $status = 'done';
        } elseif ($rand <= 75) {
            $status = 'inprogress';
        } else {
            $status = 'todo';
        }

        return [
            'id' => Str::uuid()->toString(),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'status' => $status,
            'priority' => $this->faker->randomElement(['low', 'medium', 'high', 'urgent']),
            'phase' => $this->faker->randomElement(['Perencanaan', 'Pelaksanaan', 'Evaluasi']),
            'start_datetime' => now()->subDays(rand(1, 30)),
            'due_datetime' => now()->addDays(rand(1, 30)),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
