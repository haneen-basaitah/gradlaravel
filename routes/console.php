<?php

use Illuminate\Support\Facades\Artisan;
use App\Jobs\MedicationSystemJob;

Artisan::command('medication:run', function () {
    // Dispatch your job
    dispatch(new MedicationSystemJob());
})->everyMinute();
