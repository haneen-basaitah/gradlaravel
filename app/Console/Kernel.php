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







                   // ✅ المهمة الثانية: إرسال تنبيه صلاحية الأدوية
    // $schedule->call(function () {
    //     Log::info("🚀 بدأت وظيفة إرسال تنبيهات صلاحية الأدوية");

    //     $caregivers = \App\Models\User::all();

    //     foreach ($caregivers as $caregiver) {
    //         $medications = \App\Models\Medication::whereHas('patient', function ($query) use ($caregiver) {
    //             $query->where('caregiver_email', $caregiver->email);
    //         })->whereDate('expiration_date', now()->addDays(10)->toDateString())->get();

    //         foreach ($medications as $medication) {
    //             Mail::to($caregiver->email)->send(new \App\Mail\MedicationExpiryWarning($medication));
    //             Log::info("📧 تم إرسال إيميل تنبيه انتهاء الصلاحية إلى: {$caregiver->email}");
    //         }
    //     }
    // })->daily(); // 👉 يشغلها مرة يوميًا
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
