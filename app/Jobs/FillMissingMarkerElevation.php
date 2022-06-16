<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FillMissingMarkerElevation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    //protected $map;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->map = $map;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \App\Models\Marker::where('elevation', null)->chunkById(50, function ($markers) {

            $coordinates = [];

            // For each marker, get the x and y attribute
            $markers->each(function ($marker) use (&$coordinates) {
                array_push($coordinates, $marker->x . "," . $marker->y);
            });

            $coordinates = implode("|", $coordinates);

            // Call the api https://api.open-elevation.com/api/v1/lookup?locations=41.161758,-8.583933|41.161758,-8.583933
            $api_url = 'https://api.open-elevation.com/api/v1/lookup?locations=' . $coordinates;
            $elevationResults = json_decode(file_get_contents($api_url), true);

            // Loop through the results and update the elevation. The results are in the same order as the coordinates.
            foreach ($elevationResults['results'] as $key => $result) {
                $marker = $markers[$key];
                $marker->elevation = $result['elevation'];
                $marker->save();
            }
        });
    }
}
