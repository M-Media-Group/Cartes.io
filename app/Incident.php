<?php

namespace App;

use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'redirect_to',
        'category_id',
        'user_id',
        'location',
    ];
    // protected $appends = ['location'];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('area', function (Builder $builder) {
            $builder->addSelect(DB::raw('id, X(`location`) as x, Y(`location`) as y, category_id, user_id, created_at, updated_at'));
        });

        static::addGlobalScope('recent', function (Builder $builder) {
            $builder->where('updated_at', '>',
                Carbon::now()->subMinutes(59)->toDateTimeString()
            );
        });
    }

    public function views()
    {
        return $this->hasMany('App\IncidentView');
    }

    public function category()
    {
        return $this->belongsTo('App\Incident');
    }
    // public function getLocationAttribute()
    // {
    //     return $this->addSelect(DB::raw('id, X(`location`) as x, Y(`location`) as y'));
    // }
}
