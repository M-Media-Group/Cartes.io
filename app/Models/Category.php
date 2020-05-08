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

    public function incidents()
    {
        return $this->hasMany(\App\Models\Incident::class);
    }

    // public function maps()
    // {
    //     return $this->hasManyThrough(\App\Models\Map::class, \App\Models\Incident::class);
    // }

    public function views()
    {
        return $this->hasMany(\App\Models\CategoryView::class);
    }
}
