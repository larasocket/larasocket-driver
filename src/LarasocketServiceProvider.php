<?php


namespace Exzachly\Larasocket;


use function base_path;
use Exzachly\Larasocket\Broadcasting\Broadcasters\LarasocketBroadcaster;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class LarasocketServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(BroadcastManager $broadcastManager)
    {
        $this->publishes([
            __DIR__.'/../config/larasocket.php' => base_path('config/larasocket.php'),
        ], 'config');

        $broadcastManager->extend('larasocket', function (Application $app, array $config) {
            return $app->make(LarasocketBroadcaster::class, [
                'config' => $config,
            ]);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/larasocket.php', 'larasocket');

        $this->app->singleton('larasocket.manager', LarasocketManager::class);
    }
}
