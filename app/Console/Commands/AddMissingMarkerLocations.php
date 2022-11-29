<?php

namespace App\Console\Commands;

use App\Jobs\AddMissingMarkerLocations as JobsAddMissingMarkerLocations;
use Illuminate\Console\Command;

class AddMissingMarkerLocations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:locations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add any missing locations to the new multi-location array';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        dispatch(new JobsAddMissingMarkerLocations());
        return 0;
    }
}
