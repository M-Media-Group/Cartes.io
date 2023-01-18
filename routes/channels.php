<?php

use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Broadcast;
use App\Models\Map;
use App\Models\User;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('maps.{mapId}', function (?User $user, Map $mapId) {
    // Check the map policy
    if ($user->can('view', $mapId)) {
        // If the user does not have an email (e.g. they are a guest), the username should be Anonymous + [4 random numbers]
        if (!$user->email_verified_at) {
            $user->username = 'Anonymous ' . Str::random(4);
        }

        // If the user does have an email but their profile is not set to public, then we should return "Cartes.io user + [4 random numbers]"
        else if (!$user->is_public) {
            $user->username = 'Cartes.io user ' . Str::random(4);
        }

        // Return the user resource
        return [
            'user' => UserResource::make($user),
            'socket_id' => request()->socket_id,
        ];
    }
    return false;
}, ['guards' => ['api']]);
