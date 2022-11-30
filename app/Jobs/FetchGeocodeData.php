<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\Middleware\RateLimitedWithRedis;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchGeocodeData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $location;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(\App\Models\MarkerLocation $location)
    {
        $this->location = $location;
    }

    /**
     * Get the middleware the job should pass through. Note the API usage policy states 1 call per second; this middleware will do this.
     *
     * @return array
     */
    public function middleware()
    {
        if (config('queue.default') === 'redis') {
            return [new RateLimitedWithRedis('geocodeData')];
        }
        return [new RateLimited('geocodeData')];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new Client(['headers' => ['Accept-Language' => 'en']]);

        try {
            $response = $client->get('https://nominatim.openstreetmap.org/reverse?format=geojson&lat=' . $this->location->y . '&lon=' . $this->location->x);

            // If there was an error, stop
            if ($response->getStatusCode() !== 200) {
                // Throw an exception
                throw new \Exception('Error calling Nominatim API - returned code ' . $response->getStatusCode());
            }

            $geocodeResult = json_decode($response->getBody()->getContents(), false);

            // This usually happens when a marker is in the middle of nowhere in the ocean
            if (property_exists($geocodeResult, 'error')) {
                $this->location->geocode = $geocodeResult ?? [];
                $this->location->save();
                return;
            }

            $this->location->address = optional($geocodeResult->features)[0]->properties->display_name ?? null;

            // Note that we use an empty array if no results are found - this is because we need to change the value from NULL to something else to know that we have already attempted to geocode the given location
            $this->location->geocode = $geocodeResult ?? [];

            $this->location->save();
        } catch (\Throwable $th) {
            Log::error("Could not get Nominatim data", [$th]);
        }
    }
}
