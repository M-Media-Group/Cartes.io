<?php

namespace App\Policies;

use App\Models\Map;
use App\Models\Marker;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MarkerPolicy
{
    use HandlesAuthorization;

    public function before($user, $ability)
    {
        if ($user->hasPermissionTo('manage markers')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the marker.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Marker  $marker
     * @return mixed
     */
    public function index( ? User $user, Map $map, $token = null)
    {
        if ($map->privacy !== 'private') {
            return true;
        } elseif ($token == $map->token) {
            return true;
        } elseif ($user && $map->user_id == $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create markers.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create( ? User $user, Map $map, $token = null)
    {

        if (request()->is('api*')) {
            $user = request()->user('api');
        }

        if ($token == $map->token) {
            return true;
        } elseif ($map->users_can_create_markers == 'yes') {
            return true;
        } elseif ($map->users_can_create_markers == 'only_logged_in') {
            if (!$user) {
                return false;
            }
            return $user->hasVerifiedEmail() && $user->can('create markers');
        } elseif ($user && $map->user_id == $user->id) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can create markers.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function createInBulk( ? User $user, Map $map, $token = null)
    {

        if (request()->is('api*')) {
            $user = request()->user('api');
            if (!$user) {
                return false;
            }
        }

        return $user->hasVerifiedEmail() && $user->can('create markers in bulk');

    }

    /**
     * Determine whether the user can update the marker.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Marker  $marker
     * @return mixed
     */
    public function update(User $user, Marker $marker)
    {
        if ($marker->user_id == $user->id) {
            return true;
        }
        if ($marker->token == request()->input('token')) {
            return true;
        }

        return $user->can('edit markers');
    }

    /**
     * Determine whether the user can delete the marker.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Marker  $marker
     * @return mixed
     */
    public function delete(User $user, Marker $marker)
    {
        if ($marker->user_id == $user->id) {
            return true;
        }
        if ($marker->token == request()->input('token')) {
            return true;
        }

        return $user->can('delete markers');
    }

    /**
     * Determine whether the user can restore the marker.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Marker  $marker
     * @return mixed
     */
    public function restore(User $user, Marker $marker)
    {
        return $user->can('delete markers');
    }

    /**
     * Determine whether the user can permanently delete the marker.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Marker  $marker
     * @return mixed
     */
    public function forceDelete( ? User $user, Marker $marker, $map_token = null)
    {
        if ($map_token == $marker->map->token) {
            return true;
        } elseif ($user && $marker->user_id == $user->id) {
            return true;
        } elseif ($marker->token == request()->input('token')) {
            return true;
        } elseif (!$user) {
            return false;
        }

        return $user->can('delete markers');
    }
}
