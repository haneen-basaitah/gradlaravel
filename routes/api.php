<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClosetController;

Route::post('/update-closet', [ClosetController::class, 'updateFromAPI']);
