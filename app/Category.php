<?php

namespace App;

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

    public function user()
    {
        return $this->belongsToMany('App\User');
    }

    public function incidents()
    {
        return $this->hasMany('App\Incident');
    }

    public function views()
    {
        return $this->hasMany('App\CategoryView');
    }
}
