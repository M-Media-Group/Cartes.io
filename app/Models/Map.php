<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
        'users_can_create_markers',
        'token',
        'options',
        'uuid',
    ];
    protected $hidden = ['id', 'token', 'user_id'];
    protected $casts = [
        'options' => 'array',
        'created_at' => 'datetime:c',
        'updated_at' => 'datetime:c',
    ];
    // protected $dateFormat = 'c';

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

    public function markers()
    {
        return $this->hasMany(\App\Models\Marker::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    // public function expired_markers()
    // {
    //     return $this->hasMany(\App\Models\Marker::class);
    // }

    public function categories()
    {
        return $this->belongsToMany(\App\Models\Category::class, 'markers')->wherePivot('expires_at', '>', Carbon::now()->toDateTimeString())->orWherePivot('expires_at', null)->selectRaw('categories.id, categories.name, categories.icon, count(markers.id) as markers_count')->groupBy('name', 'map_id', 'id', 'icon', 'category_id');
    }

    /**
     * Simple collaborative  filtering.
     *
     * @see https://arctype.com/blog/collaborative-filtering-tutorial/ - Thanks arctype!
     * @return Collection
     */
    public function getRelatedMapsAttribute()
    {
        // return $this->related;
        return $this->query()->join("markers", function ($join) {
            $join->on("markers.map_id", "=", "maps.id");
        })
            ->select("maps.*", DB::raw("COUNT(markers.category_id) as score"))
            ->whereIn("markers.category_id", function ($query) {
                $query->from("markers")
                    ->select("category_id")
                    ->where("map_id", "=", $this->id);
            })
            ->where("markers.map_id", "<>", $this->id)
            ->where("maps.privacy", "=", "public")
            ->orderBy("score", "desc")
            ->groupBy("maps.uuid", "markers.map_id")
            ->withCount("markers")
            ->with("categories")
            ->get();
    }
}
