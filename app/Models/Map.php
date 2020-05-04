<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Map extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'slug',
        'title',
        'description',
        'user_id',
        'privacy',
        'users_can_create_incidents',
        'token',
        'uuid',
    ];
    protected $hidden = ['id', 'token', 'user_id'];
    //

    /**
     *  Setup model event hooks.
     */
    public static function boot()
    {
        parent::boot();
        // self::creating(function ($model) {
        //     $model->uuid = (string) Uuid::generate(4);
        // });
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function incidents()
    {
        return $this->hasMany(\App\Models\Incident::class);
    }

    public function categories()
    {
        return $this->belongsToMany(\App\Models\Category::class, 'incidents')->wherePivot('expires_at', '>', Carbon::now()->toDateTimeString())->selectRaw('categories.id, categories.name, categories.icon, count(incidents.id) as incidents_count')->groupBy('name', 'map_id');
    }
}
