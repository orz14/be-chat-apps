<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class UserMessageReceivedEvent implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $userId, $content;
    public function __construct($userId, $content)
    {
        $this->userId = $userId;
        $this->content = $content;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->userId);
    }

    public function broadcastAs()
    {
        return 'message.received';
    }
}
