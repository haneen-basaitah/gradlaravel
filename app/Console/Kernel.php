<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use App\Models\Medication;
use Illuminate\Support\Facades\Mail;
use App\Jobs\MedicationSystemJob; // استيراد Job في الأعلى
class Kernel extends ConsoleKernel
{



    protected function schedule(Schedule $schedule)
    {
        $schedule->job(new MedicationSystemJob)
                 ->everyMinute()
                 ->name('medication_system')
                 ->withoutOverlapping(); // ✅ يمنع تكرار التنفيذ إذا كانت الوظيفة السابقة لم تكتمل
    }



















    // protected $commands = [
    //     \App\Console\Commands\MedicationReminder::class,
    // ];

    // protected function schedule(Schedule $schedule)
    // {
    //     $schedule->command('medication:reminder')
    //              ->everySecond(); // تشغيل كل دقيقة

    // }





        // $schedule->call(function () {
        //     Log::info("✅ تم تشغيل `runMedicationSystem()` من Laravel Scheduler");
        //     (new \App\Http\Controllers\MedicationController)->runMedicationSystem();
        // })->everySecond()->withoutOverlapping()->runInBackground();


    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }


}
