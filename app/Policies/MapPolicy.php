<?php

namespace App\Policies;

use App\Models\Map;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

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
    public function view( ? User $user, Map $map)
    {
        if ($map->privacy !== 'private') {
            return true;
        } elseif (request()->has('token') && $token == request()->input('token')) {
            return true;
        } elseif ($user && $map->user_id == $user->id) {
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
    public function create( ? User $user)
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
    public function update( ? User $user, Map $map)
    {
        if ($user && $map->user_id == $user->id) {
            return true;
        }
        if ($map->token == request()->input('token')) {
            return true;
        }

        return $user->can('edit maps');
    }

    /**
     * Determine whether the user can delete the map.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Map  $map
     * @return mixed
     */
    public function delete( ? User $user, Map $map)
    {
        if ($map->user_id == $user->id) {
            return true;
        }
        if ($map->token == request()->input('token')) {
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
    public function forceDelete( ? User $user, Map $map)
    {
        if ($user && $map->user_id == $user->id) {
            return true;
        }
        if ($map->token == request()->input('token')) {
            return true;
        }

        return $user->can('delete maps');
    }
}
