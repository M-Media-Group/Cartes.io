<?php

namespace App\Models;

use Carbon\Carbon;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class Marker extends Pivot
{
    use SpatialTrait;
    use HasFactory;
    use Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var bool
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
        'elevation',
        'link',
        'expires_at',
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

    protected $with = [
        'category'
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

        self::creating(function ($model) {
            $model->user_id = $model->user_id ?? (request()->user() ? request()->user()->id : null);
            $model->token = Str::random(32);

            // If an expires_at is already set, keep it
            if ($model->expires_at) {
                return;
            }
            if ($model->map_id && $model->map->options && isset($model->map->options['default_expiration_time'])) {
                $model->expires_at = Carbon::now()->addMinutes($model->map->options['default_expiration_time'])->toDateTimeString();
            } else {
                $model->expires_at = null;
            }
        });

        self::created(function ($model) {
            broadcast(new \App\Events\MarkerCreated($model))->toOthers();
        });

        /**
         * We are calling the job in saved because unlike created, saved does not execute when there is a mass save/update (so it won't dispatch a job a million times)
         *
         * @todo move to a listener
         */
        self::saved(function ($model) {
            if (!$model->elevation) {
                \App\Jobs\FillMissingMarkerElevation::dispatch();
            }
        });

        self::deleting(function ($model) {
            broadcast(new \App\Events\MarkerDeleted($model))->toOthers();
        });
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

    public function scopeActive($query)
    {
        return $query->where(
            'expires_at',
            '>',
            Carbon::now()->toDateTimeString()
        )
            ->orWhere('expires_at', null);
    }

    /**
     * Determine if the model should be searchable.
     *
     * @return bool
     */
    public function shouldBeSearchable()
    {
        return $this->map->privacy === 'public';
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return [
            'link' => $this->link,
            'description' => $this->description,
        ];
    }
}
