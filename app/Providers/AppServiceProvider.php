<?php

namespace App\Providers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Telescope\TelescopeServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // view()->composer(
        //     'components.nav',
        //     function ($view) {
        //         $view->with('categories', \App\Category::withCount('views')->orderBy('views_count', 'DESC')->take(3)->get());
        //     }
        // );
        Carbon::serializeUsing(function ($carbon) {
            return $carbon->format('c');
        });

        Schema::defaultStringLength(191);

        JsonResource::withoutWrapping();

        RateLimiter::for('geocodeData', function () {
            return Limit::perMinute(50);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }
}
