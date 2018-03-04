<?php

namespace Kabangi\MpesaLaravel;

use Illuminate\Support\ServiceProvider;
use Kabangi\Mpesa\Init as Mpesa;

class MpesaServiceProvider extends ServiceProvider{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/mpesa.php' => config_path('mpesa.php')
        ]);
    }

    /**
     * 
     * Register the application services.
     * 
     */
    public function register(){
        $this->app->bind('Kabangi\MpesaLaravel\Mpesa', function ($app) {
            $config = $app['config']->get('mpesa');
            return new Mpesa($config);
        });
    }
}
