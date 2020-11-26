<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'icon',
    ];
    protected $hidden = ['pivot', 'created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsToMany(\App\Models\User::class);
    }

    public function markers()
    {
        return $this->hasMany(\App\Models\Marker::class);
    }

    // public function maps()
    // {
    //     return $this->hasManyThrough(\App\Models\Map::class, \App\Models\Marker::class);
    // }

    public function views()
    {
        return $this->hasMany(\App\Models\CategoryView::class);
    }
}
