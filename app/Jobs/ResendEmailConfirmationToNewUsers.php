<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ResendEmailConfirmationToNewUsers implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Get all the users that have not confirmed their email address in the past week
        $users = \App\Models\User::where('email_verified_at', null)
            // Where the account was created between 1 day and 1 week ago
            ->where('seen_at', '>=', \Carbon\Carbon::now()->subWeek()->toDateTimeString())
            ->where('seen_at', '<=', \Carbon\Carbon::now()->subDay()->toDateTimeString())
            ->get();

        foreach ($users as $user) {
            $user->sendEmailVerificationNotification();
        }
    }
}
