<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use MMedia\LaravelCollaborativeFiltering\HasCollaborativeFiltering;

class Category extends Model
{
    use HasCollaborativeFiltering;
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

    /**
     * Undocumented function
     *
     * @deprecated use the relationship related() instead
     * @return void
     */
    public function getRelatedCategoriesAttribute()
    {
        return $this->getRelatedModels(\App\Models\Marker::class, 'map_id');
    }

    public function related()
    {
        return $this->hasManyRelatedThrough(\App\Models\Marker::class, 'map_id');
    }
}
