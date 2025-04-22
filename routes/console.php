<?php

use Illuminate\Support\Facades\Artisan;
use App\Jobs\MedicationSystemJob;
use App\Services\MqttClientService;
use App\Http\Controllers\MedicationSubscriptionController;
use App\Http\Controllers\ActivityMqttController;
use Illuminate\Support\Facades\Log;




Artisan::command('medication:run', function () {
    // Dispatch your job
    dispatch(new MedicationSystemJob());
})->everyMinute();

// ✅ تشغيل MQTT Listener دائم من داخل الكونسول

Artisan::command('mqtt:listen', function () {
    $mqtt = \App\Services\MqttClientService::getInstance();
    $controller = app(\App\Http\Controllers\MedicationSubscriptionController::class);

    // ✅ تعريف الاشتراكات هنا
    $mqtt->subscribe("medication/missed", [$controller, 'handleMissedMessage']);
    $mqtt->subscribe("nao/answer_report", function ($topic, $msg) {
        Log::info("📩 [MQTT] ($topic): $msg");
    });
    $mqtt->subscribe("nao/activity_end", function ($topic, $msg) {
        Log::info("📩 [MQTT] ($topic): $msg");

    });

    // ✅ الآن استمر في الاستماع للرسائل
    while (true) {
        $mqtt->loop(); // loop() تعمل حتى يحدث خطأ، عندها يُعاد الاتصال تلقائيًا
        sleep(2);      // لو حدث فصل، أعد المحاولة بعد ثانيتين
    }
});





