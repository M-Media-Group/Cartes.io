<?php

namespace App\Models;

use Carbon\Carbon;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Marker extends Pivot
{
    use SpatialTrait;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $incrementing = true;

    protected $table = 'markers';

    protected $touches = ['map'];

    protected $fillable = [
        'category_id',
        'user_id',
        'location',
        'description',
        'token',
        'map_id',
    ];
    protected $spatialFields = [
        'location',
    ];
    protected $hidden = ['token', 'user_id', 'map_id'];

    protected $dates = [
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime:c',
        'created_at' => 'datetime:c',
        'updated_at' => 'datetime:c',
        'is_spam' => 'boolean',
    ];

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

        // self::creating(function ($model) {
        //     $model->expires_at = Carbon::now()->addMinutes(180)->toDateTimeString();
        // });

        static::addGlobalScope('active', function (Builder $builder) {
            $builder
                ->where(
                    'expires_at',
                    '>',
                    Carbon::now()->toDateTimeString()
                )
                ->orWhere('expires_at', null);
        });
    }

    public function views()
    {
        return $this->hasMany(\App\Models\MarkerView::class);
    }

    public function category()
    {
        return $this->belongsTo(\App\Models\Category::class);
    }

    public function map()
    {
        return $this->belongsTo(\App\Models\Map::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
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
