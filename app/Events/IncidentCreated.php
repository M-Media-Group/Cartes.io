<?php

namespace App\Events;

use App\Incident;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class IncidentCreated implements ShouldBroadcastNow
{
    use SerializesModels;

    public $incident;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Incident $incident)
    {
        $this->incident = $incident->load('category');
    }

    // public function broadcastWith()
    // {
    //     //print_r($this->incident);
    //     return [
    //         'id' => $this->incident->id,
    //         'x' => $this->incident->X,
    //         'y' => $this->incident->y,
    //         'category_id' => $this->incident->category_id,
    //     ];
    // }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('incidents');
    }
}
