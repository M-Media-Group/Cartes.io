<?php

namespace App\Jobs;

use GuzzleHttp\Client;
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
        \App\Models\Marker::where('elevation', null)->chunkById(500, function ($markers) {

            $coordinates = [];

            $markers->each(function ($marker) use (&$coordinates) {
                $coordinates[] = [
                    'latitude' => $marker->x,
                    'longitude' => $marker->y,
                ];
            });

            $requestData['locations'] = $coordinates;

            // Call the API
            $client = new Client();
            $response = $client->post('https://api.open-elevation.com/api/v1/lookup', [
                'json' => $requestData,
            ]);

            // If there was an error, stop
            if ($response->getStatusCode() !== 200) {
                // Throw an exception
                throw new \Exception('Error calling Open Elevation API - returned ' . $response->getStatusCode());
            }

            // Call the api https://api.open-elevation.com/api/v1/lookup?locations=41.161758,-8.583933|41.161758,-8.583933
            $elevationResults = json_decode($response->getBody()->getContents(), true);

            // Loop through the results and update the elevation. The results are in the same order as the coordinates.
            foreach ($elevationResults['results'] as $key => $result) {
                $marker = $markers[$key];
                $marker->elevation = $result['elevation'];
                $marker->save();
            }
        });
    }
}
