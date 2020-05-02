<?php

namespace App\Models;

use Carbon\Carbon;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    use SpatialTrait;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id',
        'user_id',
        'location',
    ];
    protected $spatialFields = [
        'location',
    ];
    //protected $hidden = ['id', 'token', 'user_id'];

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

        static::addGlobalScope('recent', function (Builder $builder) {
            $builder->where('updated_at', '>',
                Carbon::now()->subMinutes(180)->toDateTimeString()
            );
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

    public function getXAttribute()
    {
        return $this->location->getLng();
    }

    public function getYAttribute()
    {
        return $this->location->getLat();
    }
}
