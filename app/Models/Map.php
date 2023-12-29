<?php

namespace App\Models;

use App\Helpers\MapImageGenerator;
use App\Models\Traits\Expandable;
use App\Models\Traits\Queryable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Str;
use MMedia\LaravelCollaborativeFiltering\HasCollaborativeFiltering;
use Laravel\Scout\Searchable;

class Map extends Model
{
    use HasCollaborativeFiltering;
    use HasFactory;
    use Searchable;
    use Expandable;
    use Queryable;

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

    protected $withCount = [
        'markers'
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
            $model->uuid = (string) Str::uuid();
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

    public function markers(): HasMany
    {
        return $this->hasMany(\App\Models\Marker::class);
    }

    public function activeMarkers(): HasMany
    {
        return $this->hasMany(\App\Models\Marker::class)->active();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function contributors(): HasManyThrough
    {
        return $this->hasManyThrough(\App\Models\User::class, \App\Models\Marker::class, 'map_id', 'id', 'id', 'user_id')->groupBy([
            'user_id',
            'map_id',
            'users.id',
            'users.username',
            'users.name',
            'users.surname',
            'users.email',
            'users.avatar',
            'users.description',
            'users.is_public',
            'users.created_at',
            'users.updated_at',
            'users.email_verified_at',
            'users.password',
            'users.seen_at',
            'users.remember_token',
        ]);
    }

    public function publicContributors(): HasManyThrough
    {
        return $this->contributors()->where('is_public', true)->selectOnlyPublicAttributes();
    }

    public function markerLocations(): HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Models\MarkerLocation::class,
            \App\Models\Marker::class,
            'map_id',
            'marker_id',
            'id',
            'id'
        );
    }

    /**
     * Get the average center of the map based on the markers locations
     *
     * @todo this may need a bit of rework, for example, when we want to use this as a query e.g. `?query=center.zoom>12`, it actually finds any marker with a zoom > 12, not the average zoom of the map. In that sense its equivalent to `?query=markers.locations.zoom>12`, which is not exactly the same as what would be expected from `?query=center.zoom>12`.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function center(): HasOneThrough
    {
        return $this->hasOneThrough(
            \App\Models\MarkerLocation::class,
            \App\Models\Marker::class,
            'map_id',
            'marker_id',
            'id',
            'id'
        )->selectRaw('AVG(ST_X(location)) as lng, AVG(ST_Y(location)) as lat, CAST(AVG(zoom) as UNSIGNED) as zoom, CAST(AVG(elevation) as UNSIGNED) as elevation')
            ->groupBy('map_id');
    }

    // public function expired_markers()
    // {
    //     return $this->hasMany(\App\Models\Marker::class);
    // }

    public function categories(): belongsToMany
    {
        return $this->belongsToMany(\App\Models\Category::class, \App\Models\Marker::class)
            // Unique
            ->groupBy([
                'categories.id',
                'categories.name',
                'categories.slug',
                'categories.icon',
                'categories.created_at',
                'categories.updated_at',
                'markers.category_id',
                'markers.map_id'
            ]);
    }

    public function related(): HasManyThrough
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

    public function scopePublic($query)
    {
        return $query->where("privacy", "=", "public");
    }

    public function scopePublicOrOwn($query)
    {
        return $query->where(function ($query) {
            $query->public();
            if (request()->user()) {
                $query->orWhere("user_id", "=", request()->user()->id);
            }
            return $query;
        });
    }

    public function scopeOwnOrRequestByIds($query, $ids)
    {
        return $query->where(function ($query) use ($ids) {
            $query->whereIn("uuid", $ids);
            if (request()->user()) {
                $query->orWhere("user_id", "=", request()->user()->id);
            }
            return $query;
        });
    }

    /**
     * Determine if the model should be searchable.
     *
     * @return bool
     */
    public function shouldBeSearchable()
    {
        return $this->privacy === 'public';
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'slug' => $this->slug,
            'uuid' => $this->uuid,
        ];
    }

    public function getExpandableFields()
    {
        return [
            'markers',
            'markers.category',
            'markers.locations',
            'publicContributors',
            'categories',
            'activeCategories',
            'related',
            'user',
            'activeMarkers',
            'activeMarkers.category',
            'activeMarkers.locations'
        ];
    }

    public function getBlacklistedParameters(): array
    {
        return [
            ...$this->hidden,
            'user.id',
            'markers.user_id',
            'markers.map_id',
            'markers.locations.user_id',
            'markers.currentLocation.user_id',
            'markers.bulk_insert_id',
            'activeMarkers.locations.user_id',
            'activeMarkers.bulk_insert_id',
            'activeMarkers.currentLocation.user_id',
            'center.map_id',
            'center.marker_id',
            'center.id'
        ];
    }

    /**
     * Given a width, height and zoom, generate a static image of the map. WIll use default OSM tiles, and the default marker icon.
     *
     * @param integer|null $width
     * @param integer|null $height
     * @param integer|null $zoom
     * @param string|null $output
     * @return string
     */
    public function getStaticMapImage(int $width = null, int $height = null, int $zoom = null, string $output = null)
    {
        $generatedMap = new MapImageGenerator($width, $height, $zoom, null, null, $output);
        return $generatedMap->getOrGenerateForMap($this);
    }
}
