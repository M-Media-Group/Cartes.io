<?php

namespace App\Listeners;

use Laravel\Passport\Events\AccessTokenCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendAPIWelcomeNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Laravel\Passport\Events\AccessTokenCreated  $event
     * @return void
     */
    public function handle(AccessTokenCreated $event)
    {
        // If this is the first token created for this user, send a welcome notification
        if ($event->user->tokens()->count() == 1) {
            $event->user->notify(new \App\Notifications\APIWelcomeNotification($event->user));
        }
    }
}
