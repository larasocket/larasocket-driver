<?php


namespace Exzachly\LaravelWebsockets\Facades;


use Exzachly\LaravelWebsockets\LaravelWebsocketManager;
use Illuminate\Support\Facades\Facade as LaravelFacade;

/**
 * @method static string testing()
 *
 * @see LaravelWebsocketManager
 */
class LaravelWebsocket extends LaravelFacade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-websocket.manager';
    }
}
