<?php

namespace App\Policies;

use App\Models\Map;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 *
 * @todo slowly deprecating token in favor of map_token
 */
class MapPolicy
{
    use HandlesAuthorization;

    public function before($user, $ability)
    {
        if ($user->hasPermissionTo('manage maps')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the map.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Map  $map
     * @return mixed
     */
    public function view(?User $user, Map $map)
    {
        if ($map->privacy !== 'private') {
            return true;
        }
        if ((request()->has('token') || request()->has('map_token')) && ($map->token == request()->input('token') || $map->token == request()->input('map_token'))) {
            return true;
        }
        if ($user && $map->user_id == $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create maps.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(?User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the map.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Map  $map
     * @return mixed
     */
    public function update(?User $user, Map $map)
    {
        if ($user && $map->user_id == $user->id) {
            return true;
        }
        if ($map->token == request()->input('token') || $map->token == request()->input('map_token')) {
            return true;
        }
        if ($user) {
            return $user->can('edit maps');
        }

        return false;
    }

    /**
     * Determine whether the user can delete the map.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Map  $map
     * @return mixed
     */
    public function delete(?User $user, Map $map)
    {
        if ($map->user_id == $user->id) {
            return true;
        }
        if ($map->token == request()->input('token') || $map->token == request()->input('map_token')) {
            return true;
        }

        return $user->can('delete maps');
    }

    /**
     * Determine whether the user can restore the map.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Map  $map
     * @return mixed
     */
    public function restore(User $user, Map $map)
    {
        return $user->can('delete maps');
    }

    /**
     * Determine whether the user can permanently delete the map.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Map  $map
     * @return mixed
     */
    public function forceDelete(?User $user, Map $map)
    {
        if ($user && $map->user_id == $user->id) {
            return true;
        }
        if ($map->token == request()->input('token') || $map->token == request()->input('map_token')) {
            return true;
        }
        if ($user) {
            return $user->can('delete maps');
        }

        return false;
    }
}
