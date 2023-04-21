<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Laravel\Passport\Events\AccessTokenCreated;

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
        // Get the user
        $user = \App\Models\User::find($event->userId);
        // If this is the first token created for this user, send a welcome notification
        if ($user->tokens()->count() == 1) {
            $user->notify(new \App\Notifications\APIWelcomeNotification($user));
        }
    }
}
