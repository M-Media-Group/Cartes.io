<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWeeklyMapsSummaryToUsers implements ShouldQueue
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
        // Get all the maps that have a user and markers
        $maps = \App\Models\Map::whereHas('user', function ($q) {
            $q->withoutGlobalScopes();
        })->whereHas('markers', function ($q) {
            // And where the markers were created in the last week
            $q->withoutGlobalScopes()
                ->where('created_at', '>=', \Carbon\Carbon::now()->subWeek()->toDateTimeString())
                ->where('created_at', '<=', \Carbon\Carbon::now()->toDateTimeString())
                ->whereRaw('markers.user_id != maps.user_id');
        })->get();

        // Group the maps by user
        $mapsByUser = $maps->groupBy('user_id');

        // Send a notification to each user
        foreach ($mapsByUser as $user_id => $maps) {
            $user = \App\Models\User::withoutGlobalScopes()->find($user_id);
            $user->notify(new \App\Notifications\MapsSummary($maps));
        }
    }
}
