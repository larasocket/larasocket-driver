<?php

namespace Larasocket;

use function base_path;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class LarasocketServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(BroadcastManager $broadcastManager)
    {
        $broadcastManager->extend('larasocket', function (Application $app, array $config) {
            return $app->make(LarasocketBroadcaster::class, [
                'config' => $config,
            ]);
        });
    }
}
