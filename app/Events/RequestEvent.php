<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RequestEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $user;
    public function __construct($request , $user)
    {
        $this->request = $request;
        $this->user = $user;
    }

    
    public function broadcastOn()
    {
        return new PrivateChannel('requestChannel.' .$this->request->id);
    }

    public function broadcastWith()
    {
        return ["request" => $this->request->data()];
    }
}
