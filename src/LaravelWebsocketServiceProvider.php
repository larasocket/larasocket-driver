<?php


namespace Exzachly\LaravelWebsockets;


use function base_path;
use Exzachly\LaravelWebsockets\Broadcasting\Broadcasters\LaravelWebsocketBroadcaster;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class LaravelWebsocketServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(BroadcastManager $broadcastManager)
    {
        $this->publishes([
            __DIR__.'/../config/laravel-websockets.php' => base_path('config/laravel-websockets.php'),
        ], 'config');

        $broadcastManager->extend('laravel-websocket', function (Application $app, array $config) {
            return $app->make(LaravelWebsocketBroadcaster::class, [
                'config' => $config,
            ]);
        });

        $this->commands([
            Console\InitApiGateway::class,
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-websockets.php', 'laravel-websockets');

        $this->app->singleton('laravel-websocket.manager', LaravelWebsocketManager::class);
    }
}
