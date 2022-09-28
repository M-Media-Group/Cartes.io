<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    use HandlesAuthorization;

    public function before($user, $ability)
    {
        if ($user->hasPermissionTo('manage roles', 'web')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the post.
     *
     * @param  User  $user
     * @param  Role $role
     * @return mixed
     */
    public function view(User $user, Role $role)
    {
        return false;
    }

    /**
     * Determine whether the user can create posts.
     *
     * @param  User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the post.
     *
     * @param  User  $user
     * @param  Role $role
     * @return mixed
     */
    public function update(User $user, Role $role)
    {
        return false;
    }

    /**
     * Determine whether the user can delete the post.
     *
     * @param  User  $user
     * @param  Role $role
     * @return mixed
     */
    public function delete(User $user, Role $role)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the post.
     *
     * @param  User  $user
     * @param  Role $role
     * @return mixed
     */
    public function restore(User $user, Role $role)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the post.
     *
     * @param  User  $user
     * @param  Role $role
     * @return mixed
     */
    public function forceDelete(User $user, Role $role)
    {
        return false;
    }
}
