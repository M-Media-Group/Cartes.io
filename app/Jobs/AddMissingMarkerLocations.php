<?php

namespace App\Jobs;

use App\Models\Marker;
use App\Models\MarkerLocation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

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

        $insertableData = [];

        foreach ($markers as $marker) {
            $insertableData[] =
                [
                    'marker_id' => $marker->id,
                    'location' => DB::raw("ST_GeomFromText('{$marker->location->toWkt()}', {$marker->location->srid})"),
                    'elevation' => $marker['elevation'],
                    'user_id' => $marker->user_id,
                    'created_at' => $marker->created_at,
                    'updated_at' => $marker->updated_at,
                ];
        }

        MarkerLocation::insert($insertableData);

        \App\Jobs\FillMissingMarkerElevation::dispatch();
        \App\Jobs\FillMissingLocationGeocodes::dispatch();
    }
}
