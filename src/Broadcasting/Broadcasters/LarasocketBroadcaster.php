<?php


namespace Exzachly\Larasocket\Broadcasting\Broadcasters;

use Arr;
use function array_map;
use function config;
use Http;
use Illuminate\Broadcasting\Broadcasters\Broadcaster;
use Illuminate\Broadcasting\Broadcasters\UsePusherChannelConventions;
use Illuminate\Broadcasting\BroadcastException;
use function is_array;
use function is_bool;
use function is_string;
use function json_decode;
use function json_encode;
use function response;
use Str;
use function str_replace;
use function strlen;
use function strncmp;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class LarasocketBroadcaster extends Broadcaster
{
    private const LARASOCKET_SERVER_MESSAGE_URL = 'http://localhost:8000/api/broadcast';

    use UsePusherChannelConventions;

    /**
     * Create a new broadcaster instance.
     *
     * @return void
     */
    public function __construct(array $config)
    {
    }

    /**
     * Authenticate the incoming request for a given channel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public function auth($request)
    {
        $channelName = $this->normalizeChannelName($request->channel_name);

        if ($this->isGuardedChannel($request->channel_name) &&
            ! $this->retrieveUser($request, $channelName)) {
            throw new AccessDeniedHttpException;
        }

        return parent::verifyUserCanAccessChannel(
            $request, $channelName
        );
    }

    /**
     * Return the valid authentication response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $result
     * @return mixed
     */
    public function validAuthenticationResponse($request, $result)
    {
        if (strncmp($request->channel_name, 'private', strlen('private')) === 0) {
            return $this->authPrivate(
                $request->channel_name,
                $request->connection_id
            );
        }

        $channelName = $this->normalizeChannelName($request->channel_name);

        return $this->authPresence(
            $request->channel_name,
            $request->connection_id,
            $this->retrieveUser($request, $channelName)->getAuthIdentifier(),
            $result
        );
    }

    /**
     * Broadcast the given event.
     *
     * @param  array  $channels
     * @param  string  $event
     * @param  array  $payload
     * @return void
     *
     * @throws \Illuminate\Broadcasting\BroadcastException
     */
    public function broadcast(array $channels, $event, array $payload = [])
    {
        $socket = Arr::pull($payload, 'socket');

        $response = $this->trigger(
            $this->formatChannels($channels), $event, $payload, $socket, true
        );

        if ($response->status() >= 200
            && $response->status() <= 299
            && ($json = $response->json())) {
            return $response;
        }

        throw new BroadcastException(
            is_bool($response) ? 'Failed to connect to Laravel Websockets.' : $response
        );
    }


    /**
     * A broadcast-ed event has been triggered. We need to send it over to Larasocket servers for processing and then
     * dispatching to listening clients.
     *
     * @param $channels
     * @param $event
     * @param $data
     * @param null $connectionId
     * @return \Illuminate\Http\Client\Response
     */
    public function trigger($channels, $event, $data, $connectionId = null)
    {
        if (is_string($channels) === true) {
            $channels = array($channels);
        }

        return Http::
            withToken(config('larasocket.token'))
            ->withHeaders(['Accept' => 'application/json'])
            ->post(self::LARASOCKET_SERVER_MESSAGE_URL, [
                'event' => $event,
                'channels' => $channels,
                'payload' => json_encode($data),
                'only_to_others' => $connectionId !== null, // server already has the connection id information.
            ]);
    }

    /**
     * @param string $channel
     * @param $connectionId
     * @return array
     */
    public function authPrivate(string $channel, $connectionId)
    {
        return [
            'connection_id' => $connectionId,
            'channel' => $channel,
        ];
    }

    /**
     * @param string $channel
     * @param $connectionId
     * @param $uid
     * @param $authResults
     * @return array
     */
    public function authPresence(string $channel, $connectionId, $uid, $authResults)
    {
        return [
            'connection_id' => $connectionId,
            'channel' => $channel,
            'user_id' => $uid,
            'payload' => $authResults,
        ];
    }
}
