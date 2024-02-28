<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Illuminate\Validation\ValidationException;

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
        'meta',
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
        'meta' => 'array',
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
        'zoom',
        'heading',
        'pitch',
        'roll',
        'speed',
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

    /**
     * Get the X attribute.
     *
     * Note, while there shouldn't be a case where there is no location for a marker, it could happen due to the architecture of the marker->location relationship - this is why its wrapped in an optional
     *
     */
    public function getXAttribute()
    {
        return optional($this->location)->longitude;
    }

    public function getYAttribute()
    {
        return optional($this->location)->latitude;
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


    public function getHeadingAttribute()
    {
        return optional($this->currentLocation)->heading;
    }

    public function getPitchAttribute()
    {
        return optional($this->currentLocation)->pitch;
    }

    public function getRollAttribute()
    {
        return optional($this->currentLocation)->roll;
    }

    public function getSpeedAttribute()
    {
        return optional($this->currentLocation)->speed;
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
     * Create a marker and its location in a transaction and return the new marker with the token visible
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
                'heading' => $data['heading'],
                'pitch' => $data['pitch'],
                'roll' => $data['roll'],
                'speed' => $data['speed'],
            ]);
            return $marker->refresh()->makeVisible(['token'])->loadMissing('category');
        });
    }

    /**
     * Create many markers and their locations in a transaction with a bulk insert
     *
     * @param array $data
     * @param \App\Models\Map|null $map
     * @return mixed returns the new refreshed marker with the token visible if the transaction succeeds
     * @throws \Illuminate\Validation\ValidationException|\Exception
     */
    public static function bulkInsertWithLocations(array $data, \App\Models\Map $map = null)
    {
        $insertableData = [];

        /** @var array Represents all formatted markers (formatted from incoming $data) */
        $allMarkers = [];

        $now = Carbon::now();
        $bulkInsertId = Str::uuid()->toString();

        foreach ($data as $marker) {
            $marker['bulk_insert_id'] = $bulkInsertId;

            // The dates need to be converted to Carbon instances and then to string for insertion
            $marker['created_at'] = isset($marker['created_at']) ? Carbon::parse($marker['created_at'])->toDateTimeString() : $now;
            $marker['updated_at'] = isset($marker['updated_at']) ? Carbon::parse($marker['updated_at'])->toDateTimeString() : $now;
            $marker['expires_at'] = isset($marker['expires_at']) ? Carbon::parse($marker['expires_at'])->toDateTimeString() : null;

            $marker['token'] = Str::random(32);
            $marker['user_id'] = $marker['user_id'] ?? optional(request()->user())->id;
            $marker['map_id'] = $marker['map_id'] ?? $map->id ?? null;

            $marker['category_id'] = $marker['category'] ?? Category::firstOrCreate(
                ['slug' => Str::slug($marker['category_name'])],
                ['name' => $marker['category_name'], 'icon' => '/images/marker-01.svg']
            )->id;

            $allMarkers[] = $marker;
            $insertableMarker = $marker;

            // If there is meta, we need to json_encode it
            if (isset($insertableMarker['meta'])) {
                $insertableMarker['meta'] = json_encode($insertableMarker['meta']);
            } else {
                $insertableMarker['meta'] = null;
            }

            if (!isset($insertableMarker['link'])) {
                $insertableMarker['link'] = null;
            }

            unset($insertableMarker['lat']);
            unset($insertableMarker['lng']);
            unset($insertableMarker['elevation']);
            unset($insertableMarker['current_location']);
            unset($insertableMarker['zoom']);
            unset($insertableMarker['heading']);
            unset($insertableMarker['pitch']);
            unset($insertableMarker['roll']);
            unset($insertableMarker['speed']);
            unset($insertableMarker['locations']);
            unset($insertableMarker['category']);
            unset($insertableMarker['category_name']);

            $insertableData[] = $insertableMarker;
        }

        DB::beginTransaction();

        try {
            $result = Marker::insert($insertableData);
            $markersInserted = Marker::where('bulk_insert_id', $bulkInsertId)->orderBy('id')->get();

            $positionData = [];
            $currentIteration = 0;

            foreach ($markersInserted as $markerInserted) {
                $locations = self::formatLocations($allMarkers[$currentIteration]);

                foreach ($locations as $location) {
                    $positionData[] = [
                        'marker_id' => $location['marker_id'] ?? $markerInserted->id,
                        'location' => DB::raw("ST_GeomFromText('POINT(" . $location['lng'] . " " . $location['lat'] . ")')"),
                        'elevation' => $location['elevation'] ?? null,
                        'zoom' => $location['zoom'] ?? null,
                        'heading' => $location['heading'] ?? null,
                        'pitch' => $location['pitch'] ?? null,
                        'roll' => $location['roll'] ?? null,
                        'speed' => $location['speed'] ?? null,
                        'user_id' => $location['user_id'] ?? $markerInserted->user_id,
                        'created_at' => isset($location['created_at']) ? Carbon::parse($location['created_at'])->toDateTimeString() : $markerInserted->created_at,
                        'updated_at' => isset($location['updated_at']) ? Carbon::parse($location['updated_at'])->toDateTimeString() : $markerInserted->updated_at,
                    ];
                }

                $currentIteration++;
            }

            $result = MarkerLocation::insert($positionData);

            DB::commit();

            \App\Jobs\FillMissingMarkerElevation::dispatch();
            \App\Jobs\FillMissingLocationGeocodes::dispatch();

            return $result;
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            $errorCode = $e->errorInfo[1];
            if ($errorCode == 1062) {
                return throw ValidationException::withMessages(['marker' => 'Some of the markers you submitted already exist in the database']);
            }
            return throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public static function formatLocations(array $data): array
    {

        $locations = $data['locations'] ?? [
            [
                'lat' => $data['lat'],
                'lng' => $data['lng'],
                'heading' => $data['heading'] ?? null,
                'pitch' => $data['pitch'] ?? null,
                'roll' => $data['roll'] ?? null,
                'speed' => $data['speed'] ?? null,
                'zoom' => $data['zoom'] ?? null,
                'elevation' => $data['elevation'] ?? null,
                'user_id' => $data['user_id'] ?? null,
                'created_at' => $data['created_at'] ?? null,
                'updated_at' => $data['updated_at'] ?? null,
            ]
        ];

        return $locations;
    }
}
