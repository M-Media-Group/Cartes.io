<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryPolicy
{
    use HandlesAuthorization;

    public function before($user, $ability)
    {
        if ($user->hasPermissionTo('manage categories', 'web')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the category.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewAny(?User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the category.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Category  $category
     * @return bool
     */
    public function view(?User $user, Category $category)
    {
        return true;
    }

    /**
     * Determine whether the user can create categories.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user)
    {
        return $user->hasVerifiedEmail() && $user->can('create categories');
    }

    /**
     * Determine whether the user can update the category.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Category  $category
     * @return bool
     */
    public function update(User $user, Category $category)
    {
        return $user->can('edit categories');
    }

    /**
     * Determine whether the user can delete the category.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Category  $category
     * @return bool
     */
    public function delete(User $user, Category $category)
    {
        return $user->can('delete categories');
    }

    /**
     * Determine whether the user can restore the category.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Category  $category
     * @return bool
     */
    public function restore(User $user, Category $category)
    {
        return $user->can('delete categories');
    }

    /**
     * Determine whether the user can permanently delete the category.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Category  $category
     * @return bool
     */
    public function forceDelete(User $user, Category $category)
    {
        return false;
    }
}
