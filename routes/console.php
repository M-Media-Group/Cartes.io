<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');

Artisan::command('fill-missing-marker-elevation', function () {
    $this->info('Filling missing marker elevation...');
    App\Jobs\FillMissingMarkerElevation::dispatch();
    $this->info('Done');
})->describe('Fill missing marker elevation');

Artisan::command('migrate:locations', function () {
    $this->info('Migrating locations');
    App\Jobs\AddMissingMarkerLocations::dispatch();
    $this->info('Done');
})->describe('Migrate the legacy format of locations to the new one');

Artisan::command('make:permissions', function () {
    $this->info('Making permissions');

    Artisan::call('permission:create-role admin web "manage markers|edit markers|create markers|delete markers|manage categories|edit categories|create categories|delete categories|manage user roles|manage roles|apply to report|manage maps|create markers in bulk|mark spam"');

    Artisan::call('permission:create-role editor web "manage markers|manage categories|manage maps"');

    Artisan::call('permission:create-role "power reporter" web "create markers in bulk"');

    Artisan::call('permission:create-role reporter web "edit markers|create markers|delete markers|mark spam"');

    $this->info('Done');
})->describe('Make required permissions');

Artisan::command('fill-missing-marker-geocode', function () {
    $this->info('Filling missing marker geocode data...');
    App\Jobs\FillMissingLocationGeocodes::dispatch();
    $this->info('Done');
})->describe('Fill missing marker geocode data');
