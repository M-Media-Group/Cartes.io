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

        $client = new Client(['headers' => ['Accept-Language' => 'en']]);

        foreach ($locations as $location) {

            /**
             *  If there is already a MarkerLocation with the exact same position and with geocode data, use that. Note this is part of the API usage poilicy
             *
             * @todo consider reworking this - if this is even a posibility, perhaps another one-many table could be useful. Need to consider pros and cons.
             */
            $duplicateLocation = \App\Models\MarkerLocation::withoutGlobalScopes()
                ->equals('location', $location->location)
                ->where('id', '!=', $location->id)
                ->where('geocode', '!=', null)->first();

            if ($duplicateLocation) {
                $location->address = $duplicateLocation->address;
                $location->geocode = $duplicateLocation->geocode;
                $location->save();
                continue;
            }

            try {
                $response = $client->get('https://nominatim.openstreetmap.org/reverse?format=geojson&lat=' . $location->y . '&lon=' . $location->x);

                // If there was an error, stop
                if ($response->getStatusCode() !== 200) {
                    // Throw an exception
                    throw new \Exception('Error calling Nominatim API - returned code ' . $response->getStatusCode());
                }

                $geocodeResult = json_decode($response->getBody()->getContents(), false);

                // This usually happens when a marker is in the middle of nowhere in the ocean
                if (property_exists($geocodeResult, 'error')) {
                    $location->geocode = $geocodeResult ?? [];
                    $location->save();
                    continue;
                }

                $location->address = optional($geocodeResult->features)[0]->properties->display_name ?? null;

                // Note that we use an empty array if no results are found - this is because we need to change the value from NULL to something else to know that we have already attempted to geocode the given location
                $location->geocode = $geocodeResult ?? [];

                $location->save();

                // Wait one second before the next request, in accordance with the API usage policy
                sleep(1);
            } catch (\Throwable $th) {
                Log::error("Could not get Nominatim data", [$th]);
            }
        }
    }
}
