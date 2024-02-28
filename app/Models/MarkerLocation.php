<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\SpatialBuilder;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;

// use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @method static SpatialBuilder query()
 */
class MarkerLocation extends Model
{
    use HasFactory;
    use HasSpatial;

    protected $table = 'marker_locations';

    protected $touches = ['marker'];

    protected $fillable = [
        'location',
        'elevation',
        'zoom',
        'heading',
        'pitch',
        'roll',
        'speed',
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

    /**
     *  Get the previous marker location for this marker location. We will use this in course and groundspeed calculations.
     *
     * @return MarkerLocation
     */
    public function previous()
    {
        return $this->marker->locations()->where('id', '<', $this->id)->orderBy('id', 'desc')->first();
    }

    /**
     * Get the next marker location for this marker location. We will use this in course and groundspeed calculations.
     *
     * @return MarkerLocation
     */
    public function next()
    {
        return $this->marker->locations()->where('id', '>', $this->id)->orderBy('id', 'asc')->first();
    }

    public function getXAttribute()
    {
        return $this->location->longitude;
    }

    public function getYAttribute()
    {
        return $this->location->latitude;
    }

    public function getZAttribute()
    {
        return $this->elevation;
    }

    /**
     * Get the inbound course from the previous location to this location.
     * @return float
     *
     * */
    public function getInboundCourseAttribute()
    {
        $previous = $this->previous();
        if (!$previous) {
            return null;
        }
        // Compute the distance between the previous.location and this location.location
        return self::haversineTrack($this->location, $previous->location);
    }

    /**
     * Get the outbound course from this location to the next location.
     * @return float
     *
     * */
    public function getOutboundCourseAttribute()
    {
        $next = $this->next();
        if (!$next) {
            return null;
        }
        // Compute the distance between the previous.location and this location.location using the haversineTrack
        return self::haversineTrack($this->location, $next->location);
    }

    /**
     * Get the groundspeed in meters per second between this location and the previous location.
     *
     *  @return float The groundspeed in meters per second
     */
    public function getGroundspeedAttribute()
    {
        $previous = $this->previous();
        if (!$previous) {
            return null;
        }

        // We'll use the Haversine formula to compute the distance between the two points.
        $distance = self::haversine($this->location, $previous->location);

        // Echo the distance in meters
        // echo $distance . " meters\n\n\n";

        // We'll use the difference in time between the two points to compute the time elapsed.
        $time = $previous->created_at->diffInMilliseconds($this->created_at) / 1000;

        // If the times are exactly the same, we'll return null. We can't devide by zero anyway.
        if ($time === 0) {
            return null;
        }

        // We'll use the distance and time to compute the speed.
        return round($distance / $time, 9);
    }

    /**
     * A function that computes a distance between two points using the Haversine formula.
     *
     * @param Point $point1
     * @param Point $point2
     * @return float The distance in meters
     */
    public static function haversine(Point $point1, Point $point2)
    {
        $latFrom = deg2rad($point1->latitude);
        $lonFrom = deg2rad($point1->longitude);
        $latTo = deg2rad($point2->latitude);
        $lonTo = deg2rad($point2->longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * 6371000;
    }

    /**
     * A function that computes the track between two points using the Haversine formula.
     * @param Point $point1
     * @param Point $point2
     *
     * @return float The track in degrees
     */
    public static function haversineTrack(Point $point1, Point $point2)
    {
        $latFrom = deg2rad($point1->latitude);
        $lonFrom = deg2rad($point1->longitude);
        $latTo = deg2rad($point2->latitude);
        $lonTo = deg2rad($point2->longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = atan2(sin($lonDelta) * cos($latTo), cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta));

        return fmod(rad2deg($angle) + 360, 360);
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
