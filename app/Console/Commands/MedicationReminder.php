<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\MedicationController;
class MedicationReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'medication:reminder';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'فحص الأدوية المجدولة وإرسال التذكيرات';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        dd('تم تنفيذ الأمر بنجاح');
        Log::info(" تشغيل `MedicationReminder` عبر Artisan Command");

        // استدعاء الوظيفة من `MedicationController`
        (new MedicationController)->runMedicationSystem();

        $this->info(' تم تنفيذ فحص الأدوية المجدولة بنجاح!');
    }

}
