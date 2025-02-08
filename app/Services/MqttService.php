<?php

namespace App\Services;

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

class MqttService
{
    protected $mqtt;

    public function __construct()
    {
        $server = '127.0.0.1'; // تأكد من أن هذا هو عنوان السيرفر الصحيح
        $port = 1883;
        $clientId = 'laravel_server';

        $username = ''; // ضع اسم المستخدم إن وجد
        $password = ''; // ضع كلمة المرور إن وجدت

        $connectionSettings = (new ConnectionSettings())
            ->setUsername($username ?: null) // إذا كان غير موجود لا تتركه فارغًا
            ->setPassword($password ?: null)
            ->setKeepAliveInterval(60);

        $this->mqtt = new MqttClient($server, $port, $clientId);
        $this->mqtt->connect($connectionSettings);
    }

    public function sendMessage($topic, $message)
    {
        $this->mqtt->publish($topic, $message, 0);
        $this->mqtt->disconnect();
    }
}
