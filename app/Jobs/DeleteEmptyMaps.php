<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteEmptyMaps implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \App\Models\Map::where('created_at', '<', \Carbon\Carbon::now()->subMinutes(60)->toDateTimeString())
            ->whereDoesntHave('markers', function ($q) {
                $q->withoutGlobalScopes();
            })->delete();
    }
}
