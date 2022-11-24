<?php

namespace App\Events;

use App\Models\Marker;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class MarkerCreated implements ShouldBroadcast
{
    use SerializesModels;
    use InteractsWithSockets;

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

    // public function broadcastWith()
    // {
    //     //print_r($this->marker);
    //     return [
    //         'id' => $this->marker->id,
    //         'x' => $this->marker->X,
    //         'y' => $this->marker->y,
    //         'category_id' => $this->marker->category_id,
    //     ];
    // }

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
