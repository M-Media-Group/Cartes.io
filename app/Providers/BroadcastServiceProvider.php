<?php

namespace App\Providers;

use App\Http\Middleware\AuthenticateGuestsForChannels;
use App\Http\Middleware\SetAuthDriverToApi;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Broadcast::routes([
            // "prefix" => "api",
            "middleware" => [SetAuthDriverToApi::class, 'api', AuthenticateGuestsForChannels::class],
        ]);

        require base_path('routes/channels.php');
    }
}
