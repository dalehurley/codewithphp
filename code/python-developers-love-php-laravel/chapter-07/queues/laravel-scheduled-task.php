<?php

declare(strict_types=1);

/**
 * Laravel scheduled tasks example comparing to Celery Beat.
 * 
 * File: app/Console/Kernel.php
 * Add to crontab: * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
 */

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Run daily at 9 AM
        $schedule->call(function () {
            // Send daily report
            logger()->info('Sending daily report...');
        })->dailyAt('09:00');

        // Run weekly on Monday at 2 AM
        $schedule->call(function () {
            // Cleanup old data
            logger()->info('Cleaning up old data...');
        })->weeklyOn(1, '02:00'); // 1 = Monday

        // Run job on schedule
        $schedule->job(new \App\Jobs\SendDailyReportJob)
            ->dailyAt('09:00');
    }
}

