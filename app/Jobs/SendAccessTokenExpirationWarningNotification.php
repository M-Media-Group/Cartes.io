<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendAccessTokenExpirationWarningNotification implements ShouldQueue
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
        // Find all the oauth_access_tokens that are expiring tomorrow
        $tokens = \Laravel\Passport\Token::where('expires_at', '>=', \Carbon\Carbon::now()->addDay()->toDateTimeString())
            ->where('expires_at', '<=', \Carbon\Carbon::now()->addDay()->addHour()->toDateTimeString())
            ->get();

        foreach ($tokens as $token) {
            // Send a notification to the user
            $token->user->notify(new \App\Notifications\AccessTokenExpirationWarning($token));
        }
    }
}
