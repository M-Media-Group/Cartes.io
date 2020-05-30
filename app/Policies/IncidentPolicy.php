<?php

namespace App\Policies;

use App\Models\Incident;
use App\Models\Map;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class IncidentPolicy
{
    use HandlesAuthorization;

    public function before($user, $ability)
    {
        if ($user->hasPermissionTo('manage incidents')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the incident.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Incident  $incident
     * @return mixed
     */
    public function index(?User $user, Map $map, $token = null)
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
     * Determine whether the user can create incidents.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(?User $user, Map $map, $token = null)
    {
        if ($token == $map->token) {
            return true;
        } elseif ($map->users_can_create_incidents == 'yes') {
            return true;
        } elseif ($map->users_can_create_incidents == 'only_logged_in') {
            if (request()->is('api*')) {
                $user = request()->user('api');
                if (! $user) {
                    return false;
                }
            }

            return $user->hasVerifiedEmail() && $user->can('create incidents');
        }

        return false;
    }

    /**
     * Determine whether the user can update the incident.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Incident  $incident
     * @return mixed
     */
    public function update(User $user, Incident $incident)
    {
        if ($incident->user_id == $user->id) {
            return true;
        }
        if ($incident->token == request()->input('token')) {
            return true;
        }

        return $user->can('edit incidents');
    }

    /**
     * Determine whether the user can delete the incident.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Incident  $incident
     * @return mixed
     */
    public function delete(User $user, Incident $incident)
    {
        if ($incident->user_id == $user->id) {
            return true;
        }
        if ($incident->token == request()->input('token')) {
            return true;
        }

        return $user->can('delete incidents');
    }

    /**
     * Determine whether the user can restore the incident.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Incident  $incident
     * @return mixed
     */
    public function restore(User $user, Incident $incident)
    {
        return $user->can('delete incidents');
    }

    /**
     * Determine whether the user can permanently delete the incident.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Incident  $incident
     * @return mixed
     */
    public function forceDelete(?User $user, Incident $incident, $map_token = null)
    {
        if ($map_token == $incident->map->token) {
            return true;
        } elseif ($user && $incident->user_id == $user->id) {
            return true;
        } elseif ($incident->token == request()->input('token')) {
            return true;
        } elseif (! $user) {
            return false;
        }

        return $user->can('delete incidents');
    }
}
