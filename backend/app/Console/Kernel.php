<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\MenuCache::class,
        Commands\Backup::class,
        Commands\UserInfoSeeder::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->call(function () {
        //     DB::table('recent_users')->delete();
        // })->daily();
        // * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
        $schedule->command('menuCache create --force')->timezone('Asia/Shanghai')->dailyAt('01:00')->onOneServer();
    }
}
