<?php


namespace Exzachly\LaravelWebsockets;


use function config;
use Http;
use function json_encode;
use LaravelWebsocketException;

class LaravelWebsocketManager
{
    /**
     * LaravelWebsocketManager constructor.
     */
    public function __construct()
    {

    }

    /**
     * Trigger an event by providing event name and payload.
     * Optionally provide a socket ID to exclude a client (most likely the sender).
     *
     * @param array|string $channels        A channel name or an array of channel names to publish the event on.
     * @param string       $event
     * @param mixed        $data            Event data
     * @param string|null  $socket_id       [optional]
     * @param bool         $debug           [optional]
     * @param bool         $already_encoded [optional]
     *
     * @throws LaravelWebsocketException Throws exception if $channels is an array of size 101 or above or $socket_id is invalid
     *
     *
     * @return \Illuminate\Http\Client\Response
     */
    public function trigger($channels, $event, $data, $socket_id = null, $debug = false)
    {
        if (is_string($channels) === true) {
            $channels = array($channels);
        }

        // ping the LaravelWebsocket server to broadcast a message to all the clients.
        $url = 'http://localhost:8000/api/broadcast';

        return Http::
            withToken(config('laravel-websockets.token'))
            ->withHeaders([
                'Accept' => 'application/json',
            ])
            ->post($url, [
                'event' => "\\{$event}",
                'channels' => $channels,
                'payload' => json_encode($data),
                'socket_id' => $socket_id,
            ]);
    }

    public function authPrivate(string $channel, $socketId)
    {
        return '';
    }

    public function authPresence(string $channel, $socketId, $uid, $authResults)
    {
        return '';
    }
}
