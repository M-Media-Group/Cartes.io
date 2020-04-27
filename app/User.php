<?php

namespace App;

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

    public function posts()
    {
        return $this->hasMany('App\Post');
    }

    public function seenPosts()
    {
        return $this->belongsToMany('App\Post', 'post_views');
    }

    public function postViews()
    {
        //Issue with column names in laravel 5.7 see: https://github.com/laravel/framework/issues/17918
        return $this->hasManyThrough('App\PostView', 'App\Post', 'user_id', 'post_id', 'id', 'id');
    }

    public function postViewsExcludingSelf()
    {
        //Must use whereRaw prob because issue with column in laravel 5.7 names see: https://github.com/laravel/framework/issues/17918
        return $this->hasManyThrough('App\PostView', 'App\Post', 'user_id', 'post_id', 'id', 'id')->whereRaw('posts.user_id != post_views.user_id');
    }

    public function seenCategories()
    {
        return $this->belongsToMany('App\Category', 'category_views');
    }

    public function isSuperAdmin()
    {
        return $this->id == config('blog.super_admin_id');
    }
}
