<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\MedicationController;

class MedicationSystemJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        //
    }
    public $tries = 5; // عدد المحاولات القصوى
    public $timeout = 120; // تعيين مهلة الوظيفة
    public function handle()
    {
        Log::info("تشغيل `MedicationSystemJob` من خلال الـ Queue Job");

        // استدعاء وظيفة تشغيل النظام من `MedicationController`
        (new MedicationController())->runMedicationSystem();
    }




}
