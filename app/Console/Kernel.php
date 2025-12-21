<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\GenerateWeeklySnapshot;
use App\Jobs\GenerateMonthlySnapshot;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ✅ Generate Weekly Snapshot
        // Jalan setiap Senin jam 00:30 (generate snapshot minggu lalu)
        $schedule->job(new GenerateWeeklySnapshot())
            ->weeklyOn(1, '00:30') // 1 = Monday
            ->timezone('Asia/Jakarta')
            ->withoutOverlapping();

        // ✅ Generate Monthly Snapshot
        // Jalan setiap tanggal 1 jam 01:00 (generate snapshot bulan lalu)
        $schedule->job(new GenerateMonthlySnapshot())
            ->monthlyOn(1, '01:00')
            ->timezone('Asia/Jakarta')
            ->withoutOverlapping();

        // ✅ Cleanup old snapshots (optional)
        // Hapus snapshot > 3 bulan, jalan setiap Minggu jam 03:00
        $schedule->call(function () {
            $snapshotManager = app(\App\Services\DSS\SnapshotManager::class);
            $snapshotManager->cleanup(90); // 90 days
        })
            ->weekly()
            ->sundays()
            ->at('03:00')
            ->timezone('Asia/Jakarta');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}