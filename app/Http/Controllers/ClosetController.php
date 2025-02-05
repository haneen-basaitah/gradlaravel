<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Closet;

class ClosetController extends Controller
{
    public function index()
{
    $closets = Closet::with('medications')->get();
    return view('dashboard.layout.closets.view', compact('closets'));
}
   // استقبال البيانات من API وتحديثها في قاعدة البيانات
   public function updateFromAPI(Request $request)
   {
       $validatedData = $request->validate([
           'closet_id' => 'required|exists:closets,id',
           'temperature' => 'required|numeric',
           'humidity' => 'required|numeric',
       ]);

       // تحديث بيانات الخزانة في قاعدة البيانات
       $closet = Closet::find($validatedData['closet_id']);
       $closet->temperature = $validatedData['temperature'];
       $closet->humidity = $validatedData['humidity'];
       $closet->save();

       return response()->json(['message' => 'Closet data updated successfully!'], 200);
   }
}
