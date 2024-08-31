<?php

namespace App\Console;

use App\Jobs\FetchRandomUserData;
use App\Jobs\ForceDeleteOldPosts;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [];
    /**

     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->job(new ForceDeleteOldPosts)->daily();
        $schedule->job(FetchRandomUserData::class)->everySixHours();
    }


    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
