<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Scout\Searchable;
use Illuminate\Validation\ValidationException;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

    public function getIsLinkedToUserAttribute()
    {
        return !!$this->user_id;
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

    /**
     * Validate a request for a bulk insert
     *
     * @param \Illuminate\Http\Request $request
     * @param Map $map
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return array
     */
    public static function validateRequestForBulkInsert(Request $request, Map $map): array
    {
        $validated_data = $request->validate([
            'markers' => 'required|array|min:1|max:1000',
            'markers.*.category' => 'required_without:markers.*.category_name|exists:categories,id',
            'markers.*.description' => ['nullable', 'string', 'max:191'],
            'markers.*.category_name' => ['required_without:markers.*.category', 'min:3', 'max:32', new \App\Rules\NotContainsString()],
            'markers.*.created_at' => 'nullable|date',
            'markers.*.updated_at' => 'nullable|date',
            'markers.*.expires_at' => 'nullable|date',
            'markers.*.link' => [Rule::requiredIf(optional($map->options)['links'] === "required")],
            'markers.*.meta' => 'nullable|array|max:10',
            'markers.*.meta.*' => ['nullable', 'max:255'],

            // The markers may contain the below
            'markers.*.lat' => 'required_without:markers.*.locations|numeric|between:-90,90',
            'markers.*.lng' => 'required_without:markers.*.locations|numeric|between:-180,180',
            'markers.*.heading' => 'nullable|numeric|between:0,360',
            'markers.*.pitch' => 'nullable|numeric|between:-90,90',
            'markers.*.roll' => 'nullable|numeric|between:-180,180',
            'markers.*.speed' => 'nullable|numeric|between:0,100000',
            'markers.*.zoom' => 'nullable|numeric|between:0,20',
            'markers.*.elevation' => 'nullable|numeric|between:-100000,100000',

            // Or they may contain a locations array, with each location containing the below
            'markers.*.locations' => 'array|required_without_all:markers.*.lat,markers.*.lng|min:1',
            'markers.*.locations.*.lat' => 'required|numeric|between:-90,90',
            'markers.*.locations.*.lng' => 'required|numeric|between:-180,180',
            'markers.*.locations.*.heading' => 'nullable|numeric|between:0,360',
            'markers.*.locations.*.pitch' => 'nullable|numeric|between:-90,90',
            'markers.*.locations.*.roll' => 'nullable|numeric|between:-180,180',
            'markers.*.locations.*.speed' => 'nullable|numeric|between:0,100000',
            'markers.*.locations.*.zoom' => 'nullable|numeric|between:0,20',
            'markers.*.locations.*.elevation' => 'nullable|numeric|between:-100000,100000',
            'markers.*.locations.*.created_at' => 'nullable|date',
            'markers.*.locations.*.updated_at' => 'nullable|date',
        ]);

        // The first foreach validates and prepares the marker data
        foreach ($validated_data['markers'] as $marker) {
            $locations = Marker::formatLocations($marker);
            // The first foreach validates and prepares the marker data
            foreach ($locations as $location) {
                self::validateCreate($request, $marker, $map, new Point($location['lat'], $location['lng']));
            }
        }

        return $validated_data;
    }

    public static function validateCreate(Request $request, $marker, Map $map, Point $point)
    {
        // Merge the point to the marker
        $marker['point'] = $point;

        // Instantiate a new validator instance.
        $validator = Validator::make($marker, [
            'point' => ['required'],
        ]);

        // If a link is present, check it
        $validator->sometimes('link', 'url', function ($input) use ($map) {
            return $input->link !== null && optional($map->options)['links'] && optional($map->options)['links'] !== "disabled";
        });

        $validator->sometimes(
            'point',
            [
                new \App\Rules\UniqueInRadius(
                    optional($map->options)['require_minimum_seperation_radius'] ?? 15,
                    $map->id,
                    $request->input('category')
                )
            ],
            function ($input) use ($map) {
                return !optional($map->options)['require_minimum_seperation_radius'];
            }
        );

        if (!optional($map->options)['limit_to_geographical_body_type']) {
            return $validator->validate();
        }

        return $validator->sometimes('point', [new \App\Rules\OnGeographicalBodyType($map->options['limit_to_geographical_body_type'])], function ($input) use ($map) {
            return $map->options && isset($map->options['limit_to_geographical_body_type']) && $map->options['limit_to_geographical_body_type'] != 'no';
        })->validate();
    }
}
