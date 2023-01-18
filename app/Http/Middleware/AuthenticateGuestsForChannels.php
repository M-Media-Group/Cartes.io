<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthenticateGuestsForChannels
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // If there is already a user, then we don't need to do anything.
        if ($request->user()) {
            return $next($request);
        }

        // If there is no user, then we need to authenticate the guest by creating a temporary user.
        $request->setUserResolver(function () use ($request) {
            return User::factory()->make([
                'username' => 'Anonymous ' . Str::random(10),
                'email' => null,
                'is_public' => false,
            ]);
        });
        return $next($request);
    }
}
