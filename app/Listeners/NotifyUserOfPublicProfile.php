<?php

namespace App\Listeners;

use App\Events\ProfileMadePublic;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyUserOfPublicProfile implements ShouldQueue
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
     * @param  \App\Events\ProfileMadePublic  $event
     * @return void
     */
    public function handle(ProfileMadePublic $event)
    {
        // Send a notification to the user
        $event->user->notify(new \App\Notifications\ProfileMadePublic());
    }
}
