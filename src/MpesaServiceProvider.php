<?php

namespace Kabangi\Mpesa\Laravel;

use Illuminate\Support\ServiceProvider;

class MpesaServiceProvider extends RootProvider{
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
        $this->app->bind('Kabangi\Mpesa\Laravel\Mpesa', function ($app) {
            $config = $app['config']->get('mpesa');
            return new Mpesa($config);
        });
    }
}
