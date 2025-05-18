<?php

namespace App\Services;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\Exceptions\MqttClientException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use PhpMqtt\Client\ConnectionSettings;


class MqttClientService
{

    private static $instance;
    private $mqtt;
    private $connected = false;
    private $isListening = true;
    private $subscriptions = [];

    private function __construct()
    {
        $server = env('MQTT_HOST','192.168.0.115');
        $port = env('MQTT_PORT', 1883);

        // ✅ اختيار Client ID حسب مناداة الأمر
        $calledFromListener = app()->runningInConsole() && str_contains(implode(' ', $_SERVER['argv']), 'mqtt:listen');

        $clientId = $calledFromListener
            ? env('MQTT_CLIENT_ID_LISTENER', 'laravel_mqtt_listener')
            : env('MQTT_CLIENT_ID_PUBLISHER', 'laravel_mqtt_scheduler');

        $connectionSettings = (new ConnectionSettings)
            ->setKeepAliveInterval(10)    // ✅ لتقليل فرص الفصل
            ->setConnectTimeout(5);       // ⏱️ مهلة الاتصال

        $this->mqtt = new MqttClient($server, $port, $clientId, MqttClient::MQTT_3_1);

        // ✅ Clean Session = false لحفظ الاشتراكات في حال الفصل
        $this->mqtt->connect($connectionSettings, false);
        $this->connected = true;
    }


    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new MqttClientService();
        }
        return self::$instance;
    }

    public function connect($maxRetries = 3)
    {
        $server = env('MQTT_HOST','192.168.0.115');
        $port = env('MQTT_PORT', 1883);
        $clientId = env('MQTT_CLIENT_ID', 'laravel_mqtt_scheduler');

        $connectionSettings = (new ConnectionSettings)
            ->setKeepAliveInterval(10)
            ->setConnectTimeout(5);

        $retryCount = 0;

        while ($retryCount < $maxRetries) {
            try {
                $this->mqtt = new MqttClient($server, $port, $clientId, MqttClient::MQTT_3_1);
                $this->mqtt->connect($connectionSettings, false); // CleanSession = false
                $this->connected = true;
                Log::info("✅ تم الاتصال بـ MQTT بنجاح.");
                $this->restoreSubscriptions();
                return;
            } catch (MqttClientException $e) {
                $retryCount++;
                Log::error("🔴 فشل الاتصال بـ MQTT (المحاولة $retryCount): " . $e->getMessage());
                sleep(3);
            }
        }

        Log::error("🔴 فشل الاتصال بـ MQTT بعد $maxRetries محاولات.");
        $this->connected = false;
    }

    public function isConnected()
    {
        return $this->connected;
    }

    public function publish($topic, $message)
    {
        if (!$this->isConnected()) {
            Log::warning("⚠️ MQTT غير متصل. محاولة إعادة الاتصال...");
            $this->connect();
            usleep(200000); // 200ms delay
        }

        try {
            $this->mqtt->publish($topic, $message);  // تم حذف qos و retain
            Log::info("📢 تم نشر الرسالة إلى MQTT: $topic - $message");
        } catch (\Exception $e) {
            Log::error("🔴 فشل النشر إلى `$topic`: " . $e->getMessage());

            $this->connected = false;
            $this->connect();

            if ($this->isConnected()) {
                try {
                    $this->mqtt->publish($topic, $message);  // تم الحذف هنا أيضًا
                    Log::info("✅ تم النشر بعد إعادة الاتصال: $topic - $message");
                } catch (\Exception $e2) {
                    Log::error("🔴 فشل النشر بعد إعادة الاتصال إلى `$topic`: " . $e2->getMessage());
                }
            } else {
                Log::error("🔴 MQTT لا يزال غير متصل بعد محاولة إعادة الاتصال.");
            }
        }
    }


    public function subscribe($topic, callable $callback)
    {
        $this->subscriptions[$topic] = $callback;

        if (!$this->isConnected()) {
            $this->connect();
        }

        if ($this->isConnected()) {
            try {
                $this->mqtt->subscribe($topic, function ($receivedTopic, $message) use ($callback) {
                    Log::info("📩 رسالة مستقبلة من MQTT ($receivedTopic): $message");
                    $callback($receivedTopic, $message);
                });
                Log::info("📡 تم الاشتراك في التوبيك: $topic");
            } catch (\Exception $e) {
                Log::error("❌ خطأ أثناء الاشتراك في `$topic`: " . $e->getMessage());
            }
        }
    }

    private function restoreSubscriptions()
    {
        foreach ($this->subscriptions as $topic => $callback) {
            try {
                $this->mqtt->subscribe($topic, function ($receivedTopic, $message) use ($callback) {
                    Log::info("📩 [إعادة] رسالة مستقبلة من MQTT ($receivedTopic): $message");
                    $callback($receivedTopic, $message);
                });
                Log::info("📡 [إعادة] الاشتراك في التوبيك: $topic");
            } catch (\Exception $e) {
                Log::error("❌ [إعادة] خطأ في الاشتراك بـ `$topic`: " . $e->getMessage());
            }
        }
    }

    public function loop($timeout = 0)
    {
        $startTime = time();

        while ($this->isConnected() && $this->isListening) {
            try {
                $this->mqtt->loop();
            } catch (\Exception $e) {
                Log::error("❌ خطأ أثناء تشغيل `loop()`: " . $e->getMessage());
                $this->connected = false;

                try {
                    $this->mqtt->disconnect();
                } catch (\Throwable $t) {
                    Log::warning("⚠️ فشل فصل الاتصال قبل إعادة المحاولة.");
                }

                Log::warning("⚠️ محاولة إعادة الاتصال...");
                $this->connect();

                if (!$this->isConnected()) {
                    Log::error("🔴 فشل إعادة الاتصال.");
                    break;
                }
            }

            usleep(500000);

            if ($timeout > 0 && (time() - $startTime) >= $timeout) {
                Log::info("⏳ المهلة انتهت. الخروج من `loop()`...");
                break;
            }
        }
    }


}






