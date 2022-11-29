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

    protected $hidden = ['user_id'];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->user_id = optional(request()->user())->id;
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

    public function scopeActive($query)
    {
        return $query->where(
            'expires_at',
            '>',
            Carbon::now()->toDateTimeString()
        )
            ->orWhere('expires_at', null);
    }
}
