<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class Marker extends Pivot
{
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
        'description',
        'token',
        'map_id',
        'link',
        'expires_at',
    ];

    protected $hidden = ['token', 'user_id', 'map_id', 'currentLocation', 'bulk_insert_id'];

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
        'category',
        'currentLocation'
    ];

    protected $withCount = [
        'locations'
    ];

    protected $appends = [
        'address',
        'elevation',
        'location',
        'zoom'
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
            $model->user_id = $model->user_id ?? optional(request()->user())->id;
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

        self::updated(function ($model) {
            // We only broadcast the event if the description changes. Creating a new location for a marker will emit its own MarkerUpdated event
            if ($model->isDirty('description')) {
                broadcast(new \App\Events\MarkerUpdated($model))->toOthers();
            }
        });

        self::created(function ($model) {
            broadcast(new \App\Events\MarkerCreated($model))->toOthers();
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

    public function locations()
    {
        return $this->hasMany(MarkerLocation::class, 'marker_id');
    }

    public function currentLocation()
    {
        return $this->hasOne(MarkerLocation::class, 'marker_id')->orderBy('created_at', 'desc');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function getXAttribute()
    {
        return $this->location->longitude;
    }

    public function getYAttribute()
    {
        return $this->location->latitude;
    }

    public function getLocationAttribute()
    {
        return optional($this->currentLocation)->location;
    }

    public function getElevationAttribute()
    {
        return optional($this->currentLocation)->elevation;
    }

    public function getZoomAttribute()
    {
        return optional($this->currentLocation)->zoom;
    }

    public function getAddressAttribute()
    {
        return optional($this->currentLocation)->address;
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
        return optional($this->map)->privacy === 'public';
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

    /**
     * Create a marker and its location in a transaction
     *
     * @param array $data
     * @return mixed returns the new refreshed marker with the token visible if the transaction succeeds
     */
    public static function createWithLocation(array $data)
    {
        return DB::transaction(function () use ($data) {
            $marker = static::create($data);
            $marker->currentLocation()->create([
                'location' => $data['location'],
                'elevation' => $data['elevation'],
                'zoom' => $data['zoom'],
            ]);
            return $marker->refresh()->makeVisible(['token'])->loadMissing('category');
        });
    }
}
