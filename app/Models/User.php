<?php

namespace App\Models;

use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use Notifiable;
    use HasRoles;
    use HasFactory;
    use CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'name', 'surname', 'email', 'password', 'avatar', 'is_public', 'description'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    protected static function booted()
    {
        static::updated(function ($user) {
            if ($user->isDirty('is_public') && $user->is_public) {
                event(new \App\Events\ProfileMadePublic($user));
            }
        });
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'username';
    }

    public function seenCategories()
    {
        return $this->belongsToMany(\App\Models\Category::class, 'category_views');
    }

    public function maps()
    {
        return $this->hasMany(\App\Models\Map::class);
    }

    public function publicMaps()
    {
        return $this->maps()->public();
    }

    public function markers()
    {
        return $this->hasMany(\App\Models\Marker::class);
    }

    public function activeMarkers()
    {
        return $this->hasMany(\App\Models\Marker::class)->active();
    }

    public function mapsContributedTo()
    {
        return $this->hasManyThrough(\App\Models\Map::class, \App\Models\Marker::class, 'user_id', 'id', 'id', 'map_id')->groupBy([
            'map_id',
            'user_id',
            'maps.user_id',
            'markers.user_id',
            'maps.id',
            'maps.uuid',
            'maps.slug',
            'maps.title',
            'maps.description',
            'maps.privacy',
            'maps.users_can_create_markers',
            'maps.options',
            'maps.created_at',
            'maps.updated_at',
            'maps.token',
        ]);
    }

    public function publicMapsContributedTo()
    {
        return $this->mapsContributedTo()->public();
    }

    public function isSuperAdmin()
    {
        return $this->id == config('blog.super_admin_id');
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeSelectOnlyPublicAttributes($query)
    {
        return $query->select([
            $this->getTable() . '.' . 'username',
            $this->getTable() . '.' . 'avatar',
            $this->getTable() . '.' . 'is_public',
            $this->getTable() . '.' . 'description',
            $this->getTable() . '.' . 'created_at',
            $this->getTable() . '.' . 'email_verified_at'
        ]);
    }
}
