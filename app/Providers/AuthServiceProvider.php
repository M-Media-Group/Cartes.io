<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Policy;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
        \App\Models\Category::class => \App\Policies\CategoryPolicy::class,
        \App\Models\User::class => \App\Policies\UserPolicy::class,
        'Spatie\Permission\Models\Role' => \App\Policies\RolePolicy::class,
        \App\Models\Incident::class => \App\Policies\IncidentPolicy::class,
        \App\Models\Map::class => \App\Policies\MapPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();

        // Implicitly grant "Admin" role all permissions
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('admin') || $user->isSuperAdmin()) {
                return true;
            }
        });
    }
}
