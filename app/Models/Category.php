<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    public function getRelatedCategoriesAttribute()
    {
        return $this->query()->join("markers", function ($join) {
            $join->on("markers.category_id", "=", "categories.id");
        })
            ->select("categories.name", "categories.id", DB::raw("COUNT(markers.category_id) as score"))
            ->whereIn("markers.map_id", function ($query) {
                $query->from("markers")
                    ->select("map_id")
                    ->where("category_id", "=", $this->id);
            })
            ->where("markers.category_id", "<>", $this->id)
            ->orderBy("score", "desc")
            ->groupBy("categories.id", "markers.category_id")
            ->get();
    }
}
