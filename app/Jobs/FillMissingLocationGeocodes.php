<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FillMissingLocationGeocodes implements ShouldQueue
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
        $locations = \App\Models\MarkerLocation::withoutGlobalScopes()->where('geocode', null)->get();

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

            FetchGeocodeData::dispatch($location);
        }
    }
}
