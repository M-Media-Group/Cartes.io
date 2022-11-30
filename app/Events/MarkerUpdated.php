<?php

namespace App\Events;

use App\Models\Marker;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MarkerUpdated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * The name of the queue connection to use when broadcasting the event.
     *
     * NOTE: We use this method instead of ShouldBroadcastNow because it seems that that contract causes issues with the serialization of the location (its empty). This achieves the same result but without the issues.
     *
     * @var string
     */
    public $connection = 'sync';

    public $marker;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Marker $marker)
    {
        $this->marker = $marker->load('category');
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('maps.' . $this->marker->map->uuid);
    }
}
