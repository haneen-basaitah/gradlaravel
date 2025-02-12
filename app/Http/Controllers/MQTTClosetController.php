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
    public function subscribeDHT()
    {
        $server = '10.212.63.66'; // عنوان MQTT Broker
        $port = 1883;
        $clientId = 'laravel_mqtt_client_' . uniqid();

        $connectionSettings = (new ConnectionSettings)
            ->setUsername(null)
            ->setPassword(null)
            ->setKeepAliveInterval(60)
            ->setConnectTimeout(10)
            ->setUseTls(false);

        $mqtt = new MqttClient($server, $port, $clientId);
        $mqtt->connect($connectionSettings, true);

        $mqtt->subscribe('esp32/dht', function ($topic, $message) {
            $data = json_decode($message, true);

            Log::info("📩 Received MQTT Data: " . json_encode($data));

            if (isset($data['temperature'], $data['humidity'], $data['closet_id'])) {
                // **🔹 البحث عن closet_id، وإذا لم يكن موجودًا، يتم إنشاؤه تلقائيًا**
                $closet = Closet::firstOrCreate(
                    ['id' => $data['closet_id']], // البحث عن closet_id
                    ['temperature' => null, 'humidity' => null, 'created_at' => now(), 'updated_at' => now()]
                );

                // **🔹 تحديث بيانات الخزانة**
                $closet->update([
                    'temperature' => $data['temperature'],
                    'humidity' => $data['humidity'],
                    'updated_at' => now()
                ]);

                Log::info("✅ Closet ID {$data['closet_id']} updated - Temperature: {$data['temperature']}°C, Humidity: {$data['humidity']}%");
            } else {
                Log::error("❌ Invalid data format received. Missing required fields.");
            }
        }, 0);

        $mqtt->loop(true);
    }

    public function showClosetData()
{
    $closets = Closet::all(); // جلب جميع البيانات من جدول closets
    return view('dashboard.layout.closets.view', compact('closets'));
}

}
