<?php


namespace Exzachly\Larasocket\Broadcasting\Broadcasters;

use Arr;
use function array_map;
use Exzachly\Larasocket\LarasocketManager;
use Illuminate\Broadcasting\Broadcasters\Broadcaster;
use Illuminate\Broadcasting\Broadcasters\UsePusherChannelConventions;
use Illuminate\Broadcasting\BroadcastException;
use function is_array;
use function is_bool;
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
    use UsePusherChannelConventions;

    /**
     * The Pusher SDK instance.
     *
     * @var LarasocketManager
     */
    protected $manager;

    /**
     * Create a new broadcaster instance.
     *
     * @param  LarasocketManager  $pusher
     * @return void
     */
    public function __construct(LarasocketManager $manager, array $config)
    {
        $this->manager = $manager;
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
            return $this->decodeLaravelWebsocketResponse(
                $request, $this->manager->authPrivate($request->channel_name, $request->socket_id)
            );
        }

        $channelName = $this->normalizeChannelName($request->channel_name);

        return $this->decodeLaravelWebsocketResponse(
            $request,
            $this->manager->authPresence(
                $request->channel_name, $request->socket_id,
                $this->retrieveUser($request, $channelName)->getAuthIdentifier(), $result
            )
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

        $response = $this->manager->trigger(
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
     * Get the Pusher SDK instance.
     *
     * @return LarasocketManager
     */
    public function getLaravelWebsocketManager()
    {
        return $this->manager;
    }

    /**
     * Decode the given Pusher response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $response
     * @return array
     */
    protected function decodeLaravelWebsocketResponse($request, $response)
    {
        if (! $request->input('callback', false)) {
            return json_decode($response, true);
        }

        return response()
            ->json(json_decode($response, true))
            ->withCallback($request->callback);
    }
}
