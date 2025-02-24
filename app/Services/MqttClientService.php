<?php

namespace App\Services;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\Exceptions\MqttClientException;
use Illuminate\Support\Facades\Log;

class MqttClientService
{
    private $mqtt;
    private $connected = false; // متغير لتعقب حالة الاتصال


    public function __construct()
    {
        $server = env('MQTT_HOST', '10.212.63.66'); // يمكنك تغييره لاحقًا
        $port = env('MQTT_PORT', 1883);
        $clientId = env('MQTT_CLIENT_ID', 'laravel_mqtt_scheduler');

        $this->mqtt = new MqttClient($server, $port, $clientId);
    }

    public function connect($maxRetries = 3)
    {
        $retryCount = 0;

        while ($retryCount < $maxRetries) {
            try {
                $this->mqtt->connect();
                $this->connected = true; // تحديث حالة الاتصال
                Log::info("✅ تم الاتصال بـ MQTT بنجاح.");
                return;
            } catch (MqttClientException $e) {
                $retryCount++;
                Log::error("🔴 فشل الاتصال بـ MQTT (المحاولة $retryCount): " . $e->getMessage());
                sleep(5); // إعادة المحاولة بعد 5 ثوانٍ
            }
        }

        Log::error("🔴 فشل الاتصال بـ MQTT بعد $maxRetries محاولات.");
        $this->connected = false;
    }
    public function setStreamTimeout($seconds = 60)
    {
        if ($this->mqtt->isConnected()) {
            $socket = $this->getSocket();
            if ($socket) {
                stream_set_timeout($socket, $seconds);
                Log::info("⏳ تم ضبط مهلة الـ Socket إلى {$seconds} ثانية.");
            } else {
                Log::warning("⚠️ لم يتم العثور على `Socket` لضبط المهلة.");
            }
        }
    }


private function getSocket()
{
    try {
        // استخدام الـ ReflectionClass للوصول إلى `socket` داخل `MqttClient`
        $reflection = new \ReflectionClass($this->mqtt);
        if ($reflection->hasProperty('socket')) {
            $property = $reflection->getProperty('socket');
            $property->setAccessible(true); // السماح بالوصول إلى `private` أو `protected`
            return $property->getValue($this->mqtt);
        }
    } catch (\Exception $e) {
        Log::error("❌ خطأ أثناء محاولة الحصول على Socket: " . $e->getMessage());
    }
    return null;
}

    // ✅ تعريف دالة isConnected() لحل المشكلة
    public function isConnected()
    {
        return $this->connected;
    }

public function publish($topic, $message)
{
    if (!$this->mqtt->isConnected()) {
        Log::warning("⚠️ إعادة محاولة الاتصال بـ MQTT...");
        $this->connect();
    }

    if ($this->mqtt->isConnected()) {
        try {
            $this->mqtt->publish($topic, $message);
            Log::info("📢 تم نشر الرسالة إلى MQTT: $topic - $message");
        } catch (MqttClientException $e) {
            Log::error("🔴 فشل النشر: " . $e->getMessage());
        }
    } else {
        Log::error("🔴 فشل النشر - MQTT لا يزال غير متصل!");
    }
}


public function subscribe($topic, callable $callback)
{
    if (!$this->mqtt->isConnected()) {
        Log::warning("⚠️ إعادة محاولة الاتصال بـ MQTT...");
        $this->connect();
    }

    if ($this->mqtt->isConnected()) {
        try {
            Log::info("📡 الاشتراك في التوبيك: $topic");

            $this->mqtt->subscribe($topic, function ($receivedTopic, $message) use ($callback) {
                Log::info("📩 رسالة مستقبلة من MQTT:");
                Log::info("📝 التوبيك: $receivedTopic");
                Log::info("📨 البيانات الأصلية: " . $message);

                $callback($receivedTopic, $message);
            }, 0);

            // ✅ تشغيل `loop()` في حلقة مستقلة لمنع التعارض
            Log::info("🔄 بدء `loop()` للاستماع المستمر للرسائل...");
            while (true) {
                if ($this->mqtt->isConnected()) {
                    $this->mqtt->loop();
                    usleep(500000); // انتظر 500ms لتقليل استهلاك المعالج
                } else {
                    Log::error("🔴 فقد الاتصال بـ MQTT، سيتم إعادة المحاولة...");
                    sleep(5);
                    $this->connect();
                }
            }
        } catch (\Exception $e) {
            Log::error("❌ خطأ أثناء الاشتراك: " . $e->getMessage());
            sleep(5);
            $this->subscribe($topic, $callback);
        }
    } else {
        Log::error("🔴 فشل الاشتراك - MQTT لا يزال غير متصل!");
        sleep(5);
        $this->subscribe($topic, $callback);
    }
}



public function disconnect()
{
    if ($this->isConnected()) {
        $this->mqtt->disconnect();
        $this->connected = false;
        Log::info("🔌 تم قطع الاتصال بـ MQTT.");
    } else {
        Log::warning("⚠️ لم يكن هناك اتصال نشط بـ MQTT للقطع.");
    }
}



















private $isListening = true; // متغير لتحديد حالة الاستماع

// public function stopListening()
// {
//     Log::info("🛑 تم إيقاف الاستماع لـ MQTT مؤقتًا.");
//     $this->isListening = false;
// }
// public function isListening()
// {


//     return $this->isListening;
// }


}

