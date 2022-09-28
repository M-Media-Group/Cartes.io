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
        if ($user->hasPermissionTo('manage markers', 'web')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the marker.
     *
     * @param User $user
     * @param Map $map
     * @param string|null $token
     * @return mixed
     */
    public function index(?User $user, Map $map, $token = null)
    {
        if ($map->privacy !== 'private') {
            return true;
        }
        if ($token == $map->token) {
            return true;
        }
        if ($user && $map->user_id == $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create markers.
     *
     * @param User $user
     * @return mixed
     */
    public function create(?User $user, Map $map, $token = null)
    {
        if ($token == $map->token) {
            return true;
        }
        if ($map->users_can_create_markers == 'yes') {
            return true;
        }
        if ($map->users_can_create_markers == 'only_logged_in') {
            return $user && $user->hasVerifiedEmail() && $user->can('create markers');
        }
        if ($user && $map->user_id == $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create markers.
     *
     * @param User $user
     * @return mixed
     */
    public function createInBulk(User $user, Map $map, $token = null)
    {
        if ($map->users_can_create_markers == 'no') {
            return $map->user_id == $user->id;
        }

        return $user->hasVerifiedEmail() && $user->hasPermissionTo('create markers in bulk', 'web');
    }

    /**
     * Determine whether the user can update the marker.
     *
     * @param User $user
     * @param Marker $marker
     * @return mixed
     */
    public function update(?User $user, Marker $marker)
    {
        if ($user && $marker->user_id == $user->id) {
            return true;
        } elseif ($marker->token == request()->input('token')) {
            return true;
        } elseif ($user) {
            return $user->can('edit markers');
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can delete the marker.
     *
     * @param User $user
     * @param Marker $marker
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
     * @param User $user
     * @param Marker $marker
     * @return mixed
     */
    public function restore(User $user, Marker $marker)
    {
        return $user->can('delete markers');
    }

    /**
     * Determine whether the user can permanently delete the marker.
     *
     * @param User $user
     * @param Marker $marker
     * @return mixed
     */
    public function markAsSpam(?User $user, Marker $marker, $map_token = null)
    {
        if ($user && $marker->user_id == $user->id) {
            return false;
        }
        if ($user && $marker->map->user_id == $user->id) {
            return $user->can('mark spam');
        }
        if ($marker->token == request()->input('token')) {
            return false;
        }
        if ($map_token == $marker->map->token) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can permanently delete the marker.
     *
     * @param User $user
     * @param Marker $marker
     * @return mixed
     */
    public function forceDelete(?User $user, Marker $marker, $map_token = null)
    {
        if ($map_token == $marker->map->token) {
            return true;
        }
        if ($user && $marker->user_id == $user->id) {
            return true;
        }
        if ($marker->token == request()->input('token')) {
            return true;
        }

        return $user && $user->can('delete markers');
    }
}
