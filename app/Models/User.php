<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'name', 'surname', 'email', 'password', 'avatar',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function seenCategories()
    {
        return $this->belongsToMany(\App\Models\Category::class, 'category_views');
    }

    public function maps()
    {
        return $this->hasMany(\App\Models\Map::class);
    }

    public function markers()
    {
        return $this->hasMany(\App\Models\Marker::class);
    }

    public function isSuperAdmin()
    {
        return $this->id == config('blog.super_admin_id');
    }
}
