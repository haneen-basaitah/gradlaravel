<?php

namespace App\Providers;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;


use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // $this->app->register(\PhpMqtt\Client\Laravel\MQTTServiceProvider::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ini_set('default_charset', 'UTF-8');

    }
}
