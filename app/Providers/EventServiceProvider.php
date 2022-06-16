<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            \App\Listeners\LogRegisteredUser::class,
            SendEmailVerificationNotification::class,
        ],

        'Illuminate\Auth\Events\Attempting' => [
            \App\Listeners\LogAuthenticationAttempt::class,
        ],

        'Illuminate\Auth\Events\Authenticated' => [
            \App\Listeners\LogAuthenticated::class,
        ],

        'Illuminate\Auth\Events\Login' => [
            \App\Listeners\LogSuccessfulLogin::class,
        ],

        'Illuminate\Auth\Events\Failed' => [
            \App\Listeners\LogFailedLogin::class,
        ],

        'Illuminate\Auth\Events\Logout' => [
            \App\Listeners\LogSuccessfulLogout::class,
        ],

        'Illuminate\Auth\Events\Lockout' => [
            \App\Listeners\LogLockout::class,
        ],

        'Illuminate\Auth\Events\PasswordReset' => [
            \App\Listeners\LogPasswordReset::class,
        ],

        \App\Events\MarkerCreated::class => [],

        \App\Events\MarkerDeleted::class => [],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
