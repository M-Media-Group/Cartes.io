<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Webpatser\Uuid\Uuid;
use MMedia\LaravelCollaborativeFiltering\HasCollaborativeFiltering;

class Map extends Model
{
    use HasCollaborativeFiltering, HasFactory;
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
    protected $appends = [
        'is_linked_to_user'
    ];
    // protected $dateFormat = 'c';

    //

    /**
     *  Setup model event hooks.
     */
    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->token = Str::random(32);
            $model->uuid = (string) Uuid::generate(4);
            $model->user_id = $model->user_id ?? (request()->user() ? request()->user()->id : null);
            $model->slug = $model->slug ?? Str::slug($model->uuid);
            $model->users_can_create_markers = $model->users_can_create_markers ?? 'only_logged_in';
            $model->privacy = $model->privacy ?? 'unlisted';
        });

        self::saving(function ($model) {
            $model->description = clean($model->description);
        });
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

    public function contributors()
    {
        return $this->hasManyThrough(\App\Models\User::class, \App\Models\Marker::class);
    }

    // public function expired_markers()
    // {
    //     return $this->hasMany(\App\Models\Marker::class);
    // }

    public function categories()
    {
        return $this->belongsToMany(\App\Models\Category::class, \App\Models\Marker::class)
            ->wherePivot('expires_at', '>', Carbon::now()->toDateTimeString())
            ->orWherePivot('expires_at', null)
            ->selectRaw('categories.id, categories.name, categories.icon, count(markers.id) as markers_count')
            ->groupBy('name', 'map_id', 'id', 'icon', 'category_id');
    }

    public function related()
    {
        return $this->hasManyRelatedThrough(\App\Models\Marker::class, 'category_id')
            ->where($this->getTable() . ".privacy", "=", "public")
            ->with("categories");
    }

    public function getShouldUseNewAppAttribute()
    {
        // If there is no SPA_URL set, return false
        if (!config('app.spa_url')) {
            return false;
        }

        return true;
    }

    public function getIsLinkedToUserAttribute()
    {
        return !!$this->user_id;
    }

    /**
     * Simple collaborative  filtering.
     *
     * @deprecated use the related() relationship instead
     * @see https://arctype.com/blog/collaborative-filtering-tutorial/ - Thanks arctype!
     * @return Collection
     */
    public function getRelatedMapsAttribute()
    {
        return $this->getRelatedModels(\App\Models\Marker::class, 'category_id', function ($query) {
            return $query
                ->where($this->getTable() . ".privacy", "=", "public")
                ->with("categories");
        });
    }

    public function scopePublic($query)
    {
        return $query->where("privacy", "=", "public");
    }
}
