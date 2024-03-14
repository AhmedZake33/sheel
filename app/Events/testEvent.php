<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class testEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $message;
    public $channel;
    public function __construct($channel , $message)
    {
        $this->message = $message;
        $this->channel = $channel;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('channel.'.$this->channel->id);
    }

    public function broadcastAs()
    {
        return 'my-event';
    }

    public function broadcastWith(): array
    {
        return ['channel' => $this->channel , "message" => $this->message];
    }
}
