<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class RoomEvent implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $roomId, $content;
    public function __construct($roomId, $content)
    {
        $this->roomId = $roomId;
        $this->content = $content;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('room.' . $this->roomId);
    }

    public function broadcastAs()
    {
        return 'message.sent';
    }
}
