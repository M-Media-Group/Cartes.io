<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\SpatialBuilder;

// use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @method static SpatialBuilder query()
 */
class MarkerLocation extends Model
{
    use HasFactory;

    protected $table = 'marker_locations';

    protected $touches = ['marker'];

    protected $fillable = [
        'location',
        'elevation',
        'zoom'
    ];

    protected $casts = [
        'geocode' => 'array',
        'location' => Point::class
    ];

    protected $hidden = ['user_id', 'marker_id'];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->user_id = optional(request()->user())->id;
        });

        /**
         * We are calling the job in saved because unlike created, saved does not execute when there is a mass save/update (so it won't dispatch a job a million times)
         *
         * @todo move to a listener
         */
        static::created(function ($model) {
            if (!$model->elevation) {
                \App\Jobs\FillMissingMarkerElevation::dispatch();
            }
            if (!$model->geocode) {
                \App\Jobs\FetchGeocodeData::dispatch($model);
            }
            broadcast(new \App\Events\MarkerUpdated($model->marker))->toOthers();
        });

        static::updated(function ($model) {
            broadcast(new \App\Events\MarkerUpdated($model->marker));
        });
    }

    public function marker()
    {
        return $this->belongsTo(Marker::class);
    }

    public function getXAttribute()
    {
        return $this->location->longitude;
    }

    public function getYAttribute()
    {
        return $this->location->latitude;
    }

    public function newEloquentBuilder($query): SpatialBuilder
    {
        return new SpatialBuilder($query);
    }

    /**
     * Scope to a given address component, using the JSON `geocode` column which contains the geocode data.
     *
     * @param [type] $query
     * @param string $component
     * @param string $value
     * @param string $operator The operator to use in the query. Defaults to '='
     * @return void
     */
    public function scopeAddressComponent($query, string $component, string $value, string $operator = '=')
    {
        // Here the query is a little different because we need to lowercase the value of the component we are querying as well as the value we are comparing it to.
        // We can use the `#>>` operator to access nested JSON properties as JSON.

        // whereRaw("geocode->'features'->0->'properties'->>'{$component}' {$operator} ?", [$value]);


        return $query->where(
            'geocode->features[0]->properties->address->' . $component,
            $operator,
            $value
        );
    }

    /**
     * Scope to a given country, using the JSON `geocode` column which contains the geocode data.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $country The two letter country code (e.g. "US")
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCountryCode($query, string $country)
    {
        return $query->addressComponent('country_code', strtolower($country));
    }
}
