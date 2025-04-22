<?php

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ProfileController;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MedicationController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ActivityMqttController;
use App\Http\Controllers\ClosetController;
use App\Http\Controllers\MQTTClosetController;


Route::get('/', function () {
  //  return view('./welcome');
  return view('./frontend.index');

});

//============================================
//============ dashboard route ===============
//============================================
// Dashboard Route

Route::prefix('dashboard-panel')->name('dashboard.')->middleware('auth')->group(function () {
    Route::get('', [DashboardController::class, 'index'])->name('index');


});

// Patients Management Routes
Route::prefix('dashboard-panel/patients')->middleware('auth')->group(function () {
    Route::get('add', [PatientController::class, 'create'])->name('patients.add');
    Route::get('view', [PatientController::class, 'index'])->name('patients.view');
    Route::get('edit', [PatientController::class, 'edit'])->name('patients.edit'); // تغيير {id} إلى {healthcardno}
    Route::post('add', [PatientController::class, 'store'])->name('patients.store');
    Route::put('update/{id}', [PatientController::class, 'update'])->name('patients.update'); // تغيير {id} إلى {healthcardno}
    Route::delete('delete/{id}', [PatientController::class, 'destroy'])->name('patients.delete'); // تغيير {id} إلى {healthcardno}
});

// medications Management Routes
Route::prefix('dashboard-panel/medications')->middleware('auth')->group(function () {
    Route::get('add', [MedicationController::class, 'create'])->name('medications.add');
    Route::post('add', [MedicationController::class, 'store'])->name('medications.store'); // يجب أن يكون `POST`
    Route::get('view', [MedicationController::class, 'index'])->name('medications.view');
    Route::get('send-reminders', [MedicationController::class, 'runMedicationSystem'])->name('medications.sendReminders');
    Route::put('/update-pill-count/{id}', [MedicationController::class, 'updatePillCount'])->name('medications.updatePillCount');

});

// Activities Management Routes
Route::prefix('dashboard-panel/activities')->middleware('auth')->group(function () {
    Route::get('view', [ActivityMqttController::class, 'index'])->name('activities.view');
});

// cloest Management Routes
Route::prefix('dashboard-panel/closets')->middleware('auth')->group(function () {
    // Route::get('view', [ClosetController::class, 'index'])->name('closets.view');
    Route::get('/subscribe-dht', [MQTTClosetController::class, 'subscribeDHT']);
    Route::get('/view', [MQTTClosetController::class, 'showClosetData'])->name('closets.view'); // عرض البيانات


});

















Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
