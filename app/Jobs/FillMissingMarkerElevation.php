<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FillMissingMarkerElevation implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \App\Models\Marker::withoutGlobalScopes()->where('elevation', null)->chunkById(500, function ($markers) {
            $coordinates = [];

            $markers->each(function ($marker) use (&$coordinates) {
                $coordinates[] = [
                    'latitude' => $marker->y,
                    'longitude' => $marker->x,
                ];
            });

            $requestData['locations'] = $coordinates;

            // Call the API
            $client = new Client();

            try {
                $response = $client->post('https://api.open-elevation.com/api/v1/lookup', [
                    'json' => $requestData,
                ]);

                // If there was an error, stop
                if ($response->getStatusCode() !== 200) {
                    // Throw an exception
                    throw new \Exception('Error calling Open Elevation API - returned code '.$response->getStatusCode());
                }

                $elevationResults = json_decode($response->getBody()->getContents(), true);

                // Loop through the results and update the elevation. The results are in the same order as the coordinates.
                foreach ($elevationResults['results'] as $key => $result) {
                    $marker = $markers[$key];
                    $marker->elevation = $result['elevation'];

                    // Save the marker elevation - we skip updating the updated_at timestamp here since its not actually updated by a user, but automatically
                    $marker->save(['timestamps' => false]);
                }
            } catch (\Throwable $th) {
                Log::error('Could not get elevation data from https://api.open-elevation.com', [$th]);
            }
        });
    }
}
