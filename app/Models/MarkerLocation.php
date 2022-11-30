<?php

namespace App\Models;

use Carbon\Carbon;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Illuminate\Database\Eloquent\Relations\Pivot;


class MarkerLocation extends Model
{
    use SpatialTrait;
    use HasFactory;

    protected $table = 'marker_locations';

    protected $touches = ['marker'];

    protected $fillable = [
        'location',
        'elevation'
    ];

    protected $spatialFields = [
        'location',
    ];

    protected $casts = [
        'geocode' => 'array'
    ];

    protected $hidden = ['user_id'];

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
        return $this->location->getLng();
    }

    public function getYAttribute()
    {
        return $this->location->getLat();
    }
}
