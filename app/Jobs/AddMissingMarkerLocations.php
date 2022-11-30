<?php

namespace App\Jobs;

use App\Models\Marker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AddMissingMarkerLocations implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $markers = Marker::whereDoesntHave('currentLocation')->get();

        foreach ($markers as $marker) {
            $location = $marker->currentLocation()->create([
                'location' => $marker->location,
                'elevation' => $marker->elevation,
            ]);

            $location->user_id = $marker->user_id;
            $location->created_at = $marker->created_at;
            $location->updated_at = $marker->updated_at;

            $location->save();
        }
    }
}
