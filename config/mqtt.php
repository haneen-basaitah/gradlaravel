<?php

return [
    'default_connection' => 'local',

    'connections' => [
        'local' => [
            'host' => env('MQTT_HOST','192.168.0.148'),  // عنوان MQTT Broker
            'port' => env('MQTT_PORT', 1883),               // المنفذ الافتراضي لـ MQTT
            'client_id' => 'laravel_mqtt_client_' . uniqid(),
            'username' => env('MQTT_USERNAME', null),
            'password' => env('MQTT_PASSWORD', null),
            'clean_session' => true,
            'connect_timeout' => 60,
            'socket_timeout' => 60,
            'keep_alive_interval' => 60,
            'protocol_level' => 4,
        ],
    ],
];
