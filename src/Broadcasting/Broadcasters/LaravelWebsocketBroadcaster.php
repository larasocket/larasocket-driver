<?php


namespace Exzachly\LaravelWebsockets\Broadcasting\Broadcasters;

use Arr;
use function array_map;
use Illuminate\Broadcasting\Broadcasters\Broadcaster;
use Illuminate\Broadcasting\Broadcasters\UsePusherChannelConventions;
use Illuminate\Broadcasting\BroadcastException;
use function is_array;
use function is_bool;
use function json_encode;
use function str_replace;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class LaravelWebsocketBroadcaster extends Broadcaster
{
    use UsePusherChannelConventions;

    /**
     * @var string
     */
    private $prefix;

    public function __construct($prefix = '')
    {
        $this->prefix = $prefix;
    }

    /**
     * Authenticate the incoming request for a given channel.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function auth($request)
    {
        $channelName = $this->normalizeChannelName(
            str_replace($this->prefix, '', $request->channel_name)
        );

        if ($this->isGuardedChannel($request->channel_name) &&
            ! $this->retrieveUser($request, $channelName)) {
            throw new AccessDeniedHttpException;
        }

        return parent::verifyUserCanAccessChannel(
            $request,
            $channelName
        );
    }

    /**
     * Return the valid authentication response.
     *
     * @param \Illuminate\Http\Request $request
     * @param mixed $result
     * @return mixed
     */
    public function validAuthenticationResponse($request, $result)
    {
        if (is_bool($result)) {
            return json_encode($result);
        }

        $channelName = $this->normalizeChannelName($request->channel_name);

        return json_encode(['channel_data' => [
            'user_id' => $this->retrieveUser($request, $channelName)->getAuthIdentifier(),
            'user_info' => $result,
        ]]);
    }

    /**
     * Broadcast the given event.
     *
     * @param array $channels
     * @param string $event
     * @param array $payload
     * @return void
     */
    public function broadcast(array $channels, $event, array $payload = [])
    {
        if (empty($channels)) {
            return;
        }

//        $connection = $this->redis->connection($this->connection);

        $payload = json_encode([
            'event' => $event,
            'data' => $payload,
            'socket' => Arr::pull($payload, 'socket'),
        ]);

//        $connection->eval(
//            $this->broadcastMultipleChannelsScript(),
//            0, $payload, ...$this->formatChannels($channels)
//        );
    }

    /**
     * Get the Lua script for broadcasting to multiple channels.
     *
     * ARGV[1] - The payload
     * ARGV[2...] - The channels
     *
     * @return string
     */
//    protected function broadcastMultipleChannelsScript()
//    {
//        return <<<'LUA'
//for i = 2, #ARGV do
//  redis.call('publish', ARGV[i], ARGV[1])
//end
//LUA;
//    }

    /**
     * Format the channel array into an array of strings.
     *
     * @param  array  $channels
     * @return array
     */
//    protected function formatChannels(array $channels)
//    {
//        return array_map(function ($channel) {
//            return $this->prefix.$channel;
//        }, parent::formatChannels($channels));
//    }
}
