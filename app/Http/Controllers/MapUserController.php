<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\Map;
use Illuminate\Http\Request;

class MapUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Map $map)
    {
        //    We'll use the "delete map" policy to check if the user can delete the map. If they can delete it, they can also view the users of it. We need to explicitly pass the policy name specifically
        $this->authorize('delete', $map);

        // Return the users of the map
        return UserResource::collection($map->users);
    }
    /**
     * Display a listing of the resource. The map uuid will be in the URL
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Map $map)
    {
        //    We'll use the "delete map" policy to check if the user can delete the map. If they can delete it, they can also add users to it. We need to explicitly pass the policy name specifically
        $this->authorize('delete', $map);

        $request->validate([
            'username' => 'required|exists:users,username',
            'can_create_markers' => 'boolean',
        ]);

        // Attach the user to the map. We need to find the user by the username
        $user = \App\Models\User::where('username', $request->username)->firstOrFail();

        // If the user is already attached to the map, return a 409 response
        if ($map->users->contains($user)) {
            return response()->json(null, 409);
        }

        // The map hasMany users, so we can use the users() relationship to attach the user to the map
        $map->users()->attach($user->id, [
            'can_create_markers' => $request->can_create_markers ?? false,
        ]);

        // Return a 201 response
        return response()->json(null, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Map $map, $username)
    {
        //    We'll use the "delete map" policy to check if the user can delete the map. If they can delete it, they can also update users of it. We need to explicitly pass the policy name specifically
        $this->authorize('delete', $map);

        $request->validate([
            'can_create_markers' => 'boolean',
        ]);

        // Find the user by the username
        $user = \App\Models\User::where('username', $username)->firstOrFail();

        // Update the user's can_create_markers attribute
        $map->users()->updateExistingPivot($user->id, [
            'can_create_markers' => $request->can_create_markers ?? false,
        ]);

        // Return a 204 response
        return response()->json(null, 204);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Map $map, $username)
    {
        //    We'll use the "delete map" policy to check if the user can delete the map. If they can delete it, they can also remove users from it. We need to explicitly pass the policy name specifically
        $this->authorize('delete', $map);

        // Find the user by the username
        $user = \App\Models\User::where('username', $username)->firstOrFail();

        // Detach the user from the map
        $map->users()->detach($user->id);

        // Return a 204 response
        return response()->json(null, 204);
    }
}
