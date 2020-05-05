<?php

namespace App\Models;

use Carbon\Carbon;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Incident extends Pivot
{
    use SpatialTrait;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'incidents';
    protected $fillable = [
        'category_id',
        'user_id',
        'location',
        'token',
        'map_id',
    ];
    protected $spatialFields = [
        'location',
    ];
    protected $hidden = ['token', 'user_id', 'map_id'];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // static::addGlobalScope('area', function (Builder $builder) {
        //     $builder->addSelect(DB::raw('id, X(`location`) as x, Y(`location`) as y, category_id, user_id, created_at, updated_at'));
        // });

        self::creating(function ($model) {
            //$model->expires_at = Carbon::now()->addMinutes(180)->toDateTimeString();
        });

        static::addGlobalScope('active', function (Builder $builder) {
            $builder
                ->where('expires_at', '>',
                    Carbon::now()->toDateTimeString()
                )
                ->orWhere('expires_at', null);
        });
    }

    public function views()
    {
        return $this->hasMany(\App\Models\IncidentView::class);
    }

    public function category()
    {
        return $this->belongsTo(\App\Models\Category::class);
    }

    public function map()
    {
        return $this->belongsTo(\App\Models\Map::class);
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
