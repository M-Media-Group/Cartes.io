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
use Illuminate\Queue\Middleware\ThrottlesExceptions;
use Illuminate\Queue\Middleware\ThrottlesExceptionsWithRedis;
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
            return [new RateLimitedWithRedis('geocodeData'), (new ThrottlesExceptionsWithRedis(10, 5))->backoff(1)];
        }
        return [new RateLimited('geocodeData'), (new ThrottlesExceptions(10, 5))->backoff(1)];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        /**
         *  If there is already a MarkerLocation with the exact same position and with geocode data, use that. Note this is part of the API usage poilicy
         *
         * @todo consider reworking this - if this is even a posibility, perhaps another one-many table could be useful. Need to consider pros and cons.
         */
        $duplicateLocation = \App\Models\MarkerLocation::withoutGlobalScopes()
            ->equals('location', $this->location->location)
            ->where('zoom', '=', $this->location->zoom)
            ->where('id', '!=', $this->location->id)
            ->where('geocode', '!=', null)->first();

        if ($duplicateLocation) {
            $this->location->address = $duplicateLocation->address;
            $this->location->geocode = $duplicateLocation->geocode;
            $this->location->save();
            return;
        }

        $client = new Client(['headers' => ['Accept-Language' => 'en', 'User-Agent' => config('app.url')]]);

        $url = 'https://nominatim.openstreetmap.org/reverse?format=geojson&lat=' . $this->location->y . '&lon=' . $this->location->x;

        if ($this->location->zoom) {
            $url .= '&zoom=' . $this->location->zoom;
        }

        try {
            $response = $client->get($url);

            // If there was an error, stop
            if ($response->getStatusCode() !== 200) {
                // Throw an exception
                return Log::error("Code returned from Nominatim API not 200", [$response->getStatusCode(), $response->getHeaders(), $response->getBody()]);
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
