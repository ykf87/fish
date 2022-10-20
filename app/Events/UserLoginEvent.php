<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class UserLoginEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $data;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user, Request $request)
    {
        $this->data = [
            'uid' => $user->id,
            'ip' => $request->ip(),
            'platform' => $request->header('platform', 20),
            'version' => $request->header('version'),
            'lang' => $request->header('lang'),
            'region' => $request->header('region'),
            'device_id' => $request->header('deviceid'),
            'brand' => $request->header('brand'),
            'model' => $request->header('devicemodel'),
            'login_time' => date('Y-m-m H:i:s')
        ];
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
