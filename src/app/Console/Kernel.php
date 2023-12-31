<?php

namespace App\Console;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('mailcare:build-statistics')->daily();

        $schedule->command('mailcare:build-statistics '.Carbon::now()->toDateString())
                 ->everyFiveMinutes();

        $schedule->command('mailcare:clean')
                 ->hourly()
                 ->appendOutputTo(storage_path('logs/auto-clean.log'));

        $schedule->command('mailcare:delete')
                 ->daily()
                 ->appendOutputTo(storage_path('logs/auto-delete.log'));
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
