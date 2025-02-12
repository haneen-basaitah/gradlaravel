<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Closet;
use App\Services\MqttService;
use App\Models\Medication;
use Illuminate\Support\Facades\Log;



class ClosetController extends Controller
{
    public function index()
{

    // $closets = Closet::with('medications')->get();
    //  return view('dashboard.layout.closets.view', compact('closets'));
}

}
