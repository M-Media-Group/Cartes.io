<?php

namespace App\Events;

use App\Models\Marker;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class MarkerDeleted implements ShouldBroadcastNow
{
    use SerializesModels;
    use InteractsWithSockets;

    public $marker;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Marker $marker)
    {
        $this->marker = $marker;
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
        return new PresenceChannel('maps.'.$this->marker->map->uuid);
    }
}
