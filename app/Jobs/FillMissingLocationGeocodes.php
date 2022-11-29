<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FillMissingLocationGeocodes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $locations = \App\Models\MarkerLocation::withoutGlobalScopes()->where('geocode', null)->get();

        foreach ($locations as $location) {
            // Call the API
            $client = new Client();

            try {
                $response = $client->get('https://nominatim.openstreetmap.org/reverse?format=geojson&lat=' . $location->y . '&lon=' . $location->x);

                // If there was an error, stop
                if ($response->getStatusCode() !== 200) {
                    // Throw an exception
                    throw new \Exception('Error calling Nominatim API - returned code ' . $response->getStatusCode());
                }

                $geocodeResult = json_decode($response->getBody()->getContents(), false);

                $location->address = $geocodeResult->features[0]->properties->display_name;
                $location->geocode = $geocodeResult->features;

                $location->save();
            } catch (\Throwable $th) {
                Log::error("Could not get Nominatim data", [$th]);
            }

            // Wait one second before the next request, in accordance with the API usage policy
            sleep(1);
        }
    }
}
