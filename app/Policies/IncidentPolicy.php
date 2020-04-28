<?php

namespace App\Policies;

use App\Incident;
use App\User;
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
     * @param  \App\User  $user
     * @param  \App\Incident  $incident
     * @return mixed
     */
    public function view(User $user, Incident $incident)
    {
        return true;
    }

    /**
     * Determine whether the user can create incidents.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create( ? User $user)
    {
        //return true;
        //$user->hasVerifiedEmail();
        // if (request()->input('map_id')) {
        //     return true;
        // }
        return $user->hasVerifiedEmail() && $user->can('create incidents');
    }

    /**
     * Determine whether the user can update the incident.
     *
     * @param  \App\User  $user
     * @param  \App\Incident  $incident
     * @return mixed
     */
    public function update(User $user, Incident $incident)
    {
        return $user->can('edit incidents');
    }

    /**
     * Determine whether the user can delete the incident.
     *
     * @param  \App\User  $user
     * @param  \App\Incident  $incident
     * @return mixed
     */
    public function delete(User $user, Incident $incident)
    {
        return $user->can('delete incidents');
    }

    /**
     * Determine whether the user can restore the incident.
     *
     * @param  \App\User  $user
     * @param  \App\Incident  $incident
     * @return mixed
     */
    public function restore(User $user, Incident $incident)
    {
        return $user->can('delete incidents');
    }

    /**
     * Determine whether the user can permanently delete the incident.
     *
     * @param  \App\User  $user
     * @param  \App\Incident  $incident
     * @return mixed
     */
    public function forceDelete(User $user, Incident $incident)
    {
        return false;
    }
}
