<?php

namespace App\Services;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\Exceptions\MqttClientException;
use Illuminate\Support\Facades\Log;


class MqttClientService
{

    private $mqtt;
    private $connected = false; // متغير لتعقب حالة الاتصال
    private $isListening = true; // متغير لتحديد حالة الاستماع

    public function __construct()
    {
        $server = env('MQTT_HOST', '10.212.63.66'); // يمكنك تغييره لاحقًا
        $port = env('MQTT_PORT', 1883);
        $clientId = env('MQTT_CLIENT_ID', 'laravel_mqtt_scheduler');

        $this->mqtt = new MqttClient($server, $port, $clientId);
    }

    // ✅ الاتصال بـ MQTT
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

    // ✅ التحقق من حالة الاتصال
    public function isConnected()
    {
        return $this->connected;
    }

    // ✅ إيقاف الاستماع دون قطع الاتصال
    public function stopListening()
    {
        Log::info("🛑 تم إيقاف الاستماع لـ MQTT مؤقتًا.");
        $this->isListening = false;
    }

    // ✅ إعادة تشغيل الاستماع
    public function resumeListening()
    {
        Log::info("🔄 إعادة تشغيل الاستماع لـ MQTT...");
        $this->isListening = true;
    }

    // ✅ التحقق مما إذا كان الاستماع نشطًا
    public function isListening()
    {
        return $this->isListening;
    }

    // ✅ قطع الاتصال بـ MQTT
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

    // ✅ نشر رسالة إلى MQTT
    public function publish($topic, $message)
    {
        if (!$this->isConnected()) {
            Log::warning("⚠️ إعادة محاولة الاتصال بـ MQTT...");
            $this->connect();
        }

        if ($this->isConnected()) {
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

    // ✅ الاشتراك في موضوع MQTT
    public function subscribe($topic, callable $callback, $timeout = 30)
    {
        if (!$this->isConnected()) {
            Log::warning("⚠️ إعادة محاولة الاتصال بـ MQTT...");
            $this->connect();
        }

        if ($this->isConnected()) {
            try {
                Log::info("📡 الاشتراك في التوبيك: $topic");

                $startTime = time();
                $messageReceived = false;

                $this->mqtt->subscribe($topic, function ($receivedTopic, $message) use ($callback, &$startTime, &$messageReceived) {
                    Log::info("📩 رسالة مستقبلة من MQTT: $receivedTopic - $message");
                    $callback($receivedTopic, $message);
                    $messageReceived = true;
                    $startTime = time(); // إعادة تعيين الوقت عند استقبال رسالة
                }, 0);

                Log::info("🔄 بدء `loop()` للاستماع للرسائل...");
                while ($this->isConnected() && $this->isListening()) {
                    $this->loop(10); // تشغيل الـ Loop مع مهلة قصوى

                    // ✅ شرط الخروج: إذا انتهت المهلة دون استقبال أي رسالة
                    if (time() - $startTime >= $timeout) {
                        Log::info("⏳ انتهت المهلة للاشتراك في `$topic`.");
                        break;
                    }
                }

                // ✅ استدعاء `runMedicationSystem()` فقط إذا كان هناك دواء جديد قادم
                if (!$messageReceived && app(\App\Http\Controllers\MedicationController::class)->hasUpcomingMedications()) {
                    Log::info("📅 يوجد دواء جديد قريب، سيتم استدعاء `runMedicationSystem()`...");
                    app(\App\Http\Controllers\MedicationController::class)->runMedicationSystem();
                } else {
                    Log::info("✅ لا يوجد دواء جديد حالياً، سيتم انتظار الموعد القادم...");
                }

            } catch (\Exception $e) {
                Log::error("❌ خطأ أثناء الاشتراك: " . $e->getMessage());
            }
        } else {
            Log::error("🔴 فشل الاشتراك - MQTT لا يزال غير متصل!");
        }
    }

    // ✅ تشغيل `loop` للاستماع للرسائل
    public function loop($timeout = 10)
    {
        $startTime = time();

        while ($this->isConnected() && $this->isListening()) {
            try {
                $this->mqtt->loop();
            } catch (\Exception $e) {
                Log::error("❌ خطأ أثناء تشغيل `loop()`: " . $e->getMessage());

                // ✅ إنهاء الاستماع بأمان إذا كان الاتصال مغلقًا
                if (!$this->isConnected()) {
                    Log::warning("🔴 تم إغلاق الاتصال بـ MQTT، سيتم إيقاف `loop()`...");
                    break;
                }

                // ✅ إعادة محاولة الاتصال إذا كان ذلك ممكنًا
                Log::warning("⚠️ إعادة محاولة الاتصال بـ MQTT...");
                $this->connect();

                if (!$this->isConnected()) {
                    Log::error("🔴 فشل إعادة الاتصال بـ MQTT.");
                    break;
                }
            }

            usleep(500000); // انتظار 500 مللي ثانية

            if (time() - $startTime >= $timeout) {
                Log::info("⏳ انتهت مهلة `loop()`، سيتم إنهاء الاستماع...");
                break;
            }
        }
    }

}







