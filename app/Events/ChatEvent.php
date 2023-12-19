<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

   
    public $message;
    public $receiverUserId;
    public function __construct($message , $receiverUserId)
    {
        $this->message = $message;
        $this->receiverUserId = $receiverUserId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel("private-user-channel-{$this->receiverUserId}");
    }

    public function broadcastAs()
    {
        return 'chat';
    }
}
