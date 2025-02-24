<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

use App\Http\Controllers\MQTTClosetController;

// Artisan::command('mqtt:subscribe', function () {
//     app(MQTTClosetController::class)->subscribeDHT();
// })->describe('Subscribe to MQTT for DHT22 data');

