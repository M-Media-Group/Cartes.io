<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //     ->everyMinute();
        $schedule->command('telescope:prune')->everyMinute();
        $schedule->job(new \App\Jobs\DeleteEmptyMaps())->daily();
        $schedule->job(new \App\Jobs\FillMissingMarkerElevation())->daily();
        $schedule->job(new \App\Jobs\SendWeeklyMapsSummaryToUsers())->weeklyOn(3, '13:00');
        $schedule->job(new \App\Jobs\ResendEmailConfirmationToNewUsers())->daily();
        $schedule->job(new \App\Jobs\SendAccessTokenExpirationWarningNotification())->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
