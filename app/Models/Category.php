<?php

namespace App\Models;

use App\Traits\HasRelated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasRelated;
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

    /**
     *  Setup model event hooks.
     */
    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->slug = Str::slug($model->name);
        });
    }

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

    public function getRelatedCategoriesAttribute()
    {
        return $this->getRelatedModels('markers', 'map_id');
    }
}
