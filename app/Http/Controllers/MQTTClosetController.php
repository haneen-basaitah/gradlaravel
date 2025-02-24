<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Closet;
use Lcobucci\MQTT\Client as MQTT;
use App\Models\Medication;
use Illuminate\Support\Facades\Log;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\Message;
use PhpMqtt\Client\TopicSubscription;
class MQTTClosetController extends Controller
{

    //===================== استقبال بيانات DHT من ESP32 عبر MQTT=============
    // public function subscribeDHT()
    // {
    //     $server = '10.212.63.66'; // عنوان MQTT Broker
    //     $port = 1883;
    //     $clientId = 'laravel_mqtt_client_' . uniqid();

    //     $connectionSettings = (new ConnectionSettings)
    //         ->setUsername(null)
    //         ->setPassword(null)
    //         ->setKeepAliveInterval(60)
    //         ->setConnectTimeout(10)
    //         ->setUseTls(false);

    //     $mqtt = new MqttClient($server, $port, $clientId);
    //     $mqtt->connect($connectionSettings, true);

    //     // **🔹 الاشتراك في موضوع درجات الحرارة والرطوبة**
    //     $mqtt->subscribe('esp32/dht', function ($topic, $message) {
    //         $data = json_decode($message, true);

    //         Log::info("📩 Received DHT Data: " . json_encode($data));

    //         if (isset($data['temperature'], $data['humidity'], $data['closet_id'])) {
    //             $closet = Closet::firstOrCreate(
    //                 ['id' => $data['closet_id']],
    //                 ['temperature' => null, 'humidity' => null, 'created_at' => now(), 'updated_at' => now()]
    //             );

    //             $closet->update([
    //                 'temperature' => $data['temperature'],
    //                 'humidity' => $data['humidity'],
    //                 'updated_at' => now()
    //             ]);

    //             Log::info("✅ Closet ID {$data['closet_id']} updated - Temperature: {$data['temperature']}°C, Humidity: {$data['humidity']}%");
    //         } else {
    //             Log::error("❌ Invalid data format received for DHT data.");
    //         }
    //     }, 0);

    //     // **🔹 الاشتراك في موضوع بيانات الحبوب**
    //     $mqtt->subscribe('esp32/pill', function ($topic, $message) {
    //         Log::info("📩 Pill Intake Message Received: $message");

    //         $data = json_decode($message, true);

    //         if (isset($data['status']) && isset($data['closet_id'])) {
    //             $status = $data['status'];
    //             $closetId = $data['closet_id'];

    //             $medication = Medication::where('medicine_closet_number', $closetId)->first();

    //             if ($medication) {
    //                 if ($status === 'taken') {
    //                     if ($medication->pill_count > 0) {
    //                         $medication->pill_count -= 1;
    //                         $medication->status = 'taken';
    //                         $medication->save();

    //                         Log::info("✅ Pill taken successfully. Remaining pills: {$medication->pill_count}");
    //                     }
    //                 } elseif ($status === 'missed') {
    //                     $medication->status = 'missed';
    //                     $medication->save();

    //                     $caregiverEmail = $medication->patient->caregiver_email ?? 'default@caregiver.com';
    //                     Mail::raw("⚠️ لم يتم تناول الدواء في الوقت المحدد!", function ($message) use ($caregiverEmail) {
    //                         $message->to($caregiverEmail)->subject("🚨 تنبيه: جرعة دواء فائتة!");
    //                     });

    //                     Log::warning("⚠️ Medication missed: {$medication->name}. Caregiver notified.");
    //                 }
    //             } else {
    //                 Log::error("❌ Medication not found for closet ID: {$closetId}");
    //             }
    //         } else {
    //             Log::error("❌ Invalid message format for pill intake data.");
    //         }
    //     }, 0);

    //     $mqtt->loop(true);
    // }

    // ===================== عرض بيانات الخزانة ====================
    public function showClosetData()
    {
        $closets = Closet::all();
        return view('dashboard.layout.closets.view', compact('closets'));
    }

 }


