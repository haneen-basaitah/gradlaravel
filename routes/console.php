<?php

use Illuminate\Support\Facades\Artisan;
use App\Jobs\MedicationSystemJob;
use App\Services\MqttClientService;
use App\Http\Controllers\MedicationSubscriptionController;
use App\Http\Controllers\ActivityMqttController;
use Illuminate\Support\Facades\Log;
use App\Models\Activity;
Artisan::command('medication:run', function () {
    // Dispatch your job
    dispatch(new MedicationSystemJob());
})->everyMinute();

// ✅ تشغيل MQTT Listener دائم من داخل الكونسول

Artisan::command('mqtt:listen', function () {
    $mqtt = \App\Services\MqttClientService::getInstance();
    $controller = app(\App\Http\Controllers\MedicationSubscriptionController::class);
    $activityController = app(\App\Http\Controllers\ActivityMqttController::class); // ⬅️ هنا أضفنا هذا


    // ✅ تعريف الاشتراكات هنا
    $mqtt->subscribe("medication/missed", [$controller, 'handleMissedMessage']);
    $mqtt->subscribe("nao/answer_report", function ($topic, $msg) use ($activityController) {
        Log::info("📩 [MQTT] ($topic): $msg");
        $activityController->handleAnswerReport($msg);
    });

    $mqtt->subscribe("nao/activity_end", function ($topic, $msg) use ($activityController) {
        Log::info("📩 [MQTT] ($topic): $msg");
        $activityController->handleActivityEnd($msg);
    });


    // ✅ الآن استمر في الاستماع للرسائل
    while (true) {
        $mqtt->loop(); // loop() تعمل حتى يحدث خطأ، عندها يُعاد الاتصال تلقائيًا
        sleep(2);      // لو حدث فصل، أعد المحاولة بعد ثانيتين
    }
});





