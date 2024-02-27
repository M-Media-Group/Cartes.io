<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMarkerRequest;
use App\Http\Resources\MarkerGeoJsonCollection;
use App\Models\Map;
use App\Models\Marker;
use App\Models\MarkerLocation;
use App\Parsers\Files\GPXParser;
use Carbon\Carbon;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class MarkerController extends Controller
{
    public function __construct()
    {
        $this->middleware('throttle:markers')->only(['storeInBulk', 'store', 'update', 'destroy']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexAll(Request $request)
    {
        $data = Marker::where('is_spam', false)
            ->whereHas('map', function ($query) {
                $query->where('privacy', 'public');
            })
            ->with(['map' => function ($query) {
                $query->select('uuid', 'id');
            }])
            ->when($request->input('category_id'), function ($query) use ($request) {
                return $query->whereHas('categories', function ($query) use ($request) {
                    $query->where('category_id', $request->input('category_id'));
                });
            })
            ->when($request->input('show_expired') == 'true', function ($query) {
                return $query;
            }, function ($query) {
                return $query->active();
            })
            ->paginate();

        // If the requested format is GeoJSON, return the GeoJSON resource collection
        if ($request->input('format') === 'geojson') {
            return new MarkerGeoJsonCollection($data);
        }

        return $data;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Map $map)
    {
        $this->authorize('index', [Marker::class, $map, $request->input('map_token')]);

        $data = $map->markers();

        if ($request->input('show_expired') !== 'true') {
            $data = $data->active();
        }

        $data = $data->get();


        // If the requested format is GeoJSON, return the GeoJSON resource collection
        if ($request->input('format') === 'geojson') {
            return new MarkerGeoJsonCollection($data);
        }

        return $data;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreMarkerRequest $request, Map $map)
    {
        if (!$request->input('category')) {
            $category = \App\Models\Category::firstOrCreate(
                ['slug' => Str::slug($request->input('category_name'))],
                ['name' => $request->input('category_name'), 'icon' => '/images/marker-01.svg']
            );
            $request->merge(['category' => $category->id]);
        }

        $point = new Point($request->lat, $request->lng);

        $this->validateCreate($request, $request->input(), $map, $point);

        return Marker::createWithLocation([
            'category_id' => $request->input('category'),
            'description' => clean($request->input('description')),
            'map_id' => $map->id,
            'link' => optional($map->options)['links'] && optional($map->options)['links'] !== "disabled" ? $request->input('link') : null,
            'location' => $point,
            'zoom' => $request->input('zoom'),
            'elevation' => $request->input('elevation'),
            'expires_at' => $request->input('expires_at') ? Carbon::parse($request->input('expires_at')) : null,
            'meta' => $request->input('meta'),
            'heading' => $request->input('heading'),
            'pitch' => $request->input('pitch'),
            'roll' => $request->input('roll'),
            'speed' => $request->input('speed'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeInBulk(Request $request, Map $map)
    {
        $this->authorize('createInBulk', [Marker::class, $map, $request->input('map_token')]);

        $bulkInsertId = Str::uuid()->toString();

        $request->merge(['user_id' => $request->user()->id]);

        $validated_data = $request->validate([
            'markers' => 'required|array|min:1|max:1000',
            'markers.*.category' => 'required_without:markers.*.category_name|exists:categories,id',
            'markers.*.description' => ['nullable', 'string', 'max:191'],
            'markers.*.category_name' => ['required_without:markers.*.category', 'min:3', 'max:32', new \App\Rules\NotContainsString()],
            'user_id' => 'exists:users,id',
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

        $now = Carbon::now();

        $insertableData = [];

        foreach ($validated_data['markers'] as $index => $marker) {
            $marker['bulk_insert_id'] = $bulkInsertId;



            // The dates need to be converted to Carbon instances and then to string for insertion
            $marker['created_at'] = isset($marker['created_at']) ? Carbon::parse($marker['created_at'])->toDateTimeString() : $now;
            $marker['updated_at'] = isset($marker['updated_at']) ? Carbon::parse($marker['updated_at'])->toDateTimeString() : $now;
            $marker['expires_at'] = isset($marker['expires_at']) ? Carbon::parse($marker['expires_at'])->toDateTimeString() : null;

            $marker['token'] = Str::random(32);
            $marker['user_id'] = $validated_data['user_id'];
            $marker['map_id'] = $map->id;

            $marker['category_id'] = $marker['category'] ?? \App\Models\Category::firstOrCreate(
                ['slug' => Str::slug($marker['category_name'])],
                ['name' => $marker['category_name'], 'icon' => '/images/marker-01.svg']
            )->id;

            $locations = $marker['locations'] ??
                [
                    [
                        'lat' => $marker['lat'],
                        'lng' => $marker['lng'],
                        'heading' => $marker['heading'] ?? null,
                        'pitch' => $marker['pitch'] ?? null,
                        'roll' => $marker['roll'] ?? null,
                        'speed' => $marker['speed'] ?? null,
                        'zoom' => $marker['zoom'] ?? null,
                        'elevation' => $marker['elevation'] ?? null,
                        'user_id' => $marker['user_id'],
                        'created_at' => $marker['created_at'] ?? $now,
                        'updated_at' => $marker['updated_at'] ?? $now,
                    ]
                ];
            unset($marker['lat']);
            unset($marker['lng']);

            // The first foreach validates and prepares the marker data
            foreach ($locations as $location) {
                $this->validateCreate($request, $marker, $map, new Point($location['lat'], $location['lng']));
            }

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
            $markerIds = Marker::where('bulk_insert_id', $bulkInsertId)->get();

            $positionData = [];
            $currentIteration = 0;

            foreach ($markerIds as $marker) {

                $locations = $validated_data['markers'][$currentIteration]['locations'] ?? [
                    [
                        'lat' => $validated_data['markers'][$currentIteration]['lat'],
                        'lng' => $validated_data['markers'][$currentIteration]['lng'],
                        'heading' => $validated_data['markers'][$currentIteration]['heading'] ?? null,
                        'pitch' => $validated_data['markers'][$currentIteration]['pitch'] ?? null,
                        'roll' => $validated_data['markers'][$currentIteration]['roll'] ?? null,
                        'speed' => $validated_data['markers'][$currentIteration]['speed'] ?? null,
                        'zoom' => $validated_data['markers'][$currentIteration]['zoom'] ?? null,
                        'elevation' => $validated_data['markers'][$currentIteration]['elevation'] ?? null,
                        'created_at' => $marker->created_at,
                        'updated_at' => $marker->updated_at,
                    ]
                ];


                foreach ($locations as $location) {
                    $positionData[] = [
                        'marker_id' => $marker->id,
                        'location' => DB::raw("ST_GeomFromText('POINT(" . $location['lng'] . " " . $location['lat'] . ")')"),
                        'elevation' => $location['elevation'] ?? null,
                        'zoom' => $location['zoom'] ?? null,
                        'heading' => $location['heading'] ?? null,
                        'pitch' => $location['pitch'] ?? null,
                        'roll' => $location['roll'] ?? null,
                        'speed' => $location['speed'] ?? null,
                        'user_id' => $marker->user_id,
                        'created_at' => isset($location['created_at']) ? Carbon::parse($location['created_at'])->toDateTimeString() : $marker->created_at ?? $now,
                        'updated_at' => isset($location['updated_at']) ? Carbon::parse($location['updated_at'])->toDateTimeString() : $marker->updated_at ?? $now,
                    ];
                }

                $currentIteration++;
            }

            $result = MarkerLocation::insert($positionData);

            DB::commit();

            \App\Jobs\FillMissingMarkerElevation::dispatch();
            \App\Jobs\FillMissingLocationGeocodes::dispatch();

            return response()->json(['success' => $result]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            $errorCode = $e->errorInfo[1];
            if ($errorCode == 1062) {
                return throw ValidationException::withMessages(['marker' => 'Some of the markers you submitted already exist in the database']);
            }
            return abort(500, "Markers in bulk error code: " . $errorCode);
        }
    }

    /**
     * Store the newly created resources via a bulk insert from a file. This first parses the file and then calls the above storeInBulk method
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Map $map
     * @return \Illuminate\Http\Response
     */
    public function storeInBulkFromFile(Request $request, Map $map)
    {
        $this->authorize('createInBulk', [Marker::class, $map, $request->input('map_token')]);

        $request->validate([
            'file' => 'required|file|mimes:gpx',
        ]);

        // If the file is a GPX file, parse it and return the markers
        if ($request->file('file')->getClientOriginalExtension() === 'gpx') {
            $parser = new GPXParser();
        } else {
            return response()->json(['error' => 'File type not supported'], 422);
        }

        $markers = $parser->parseFile($request->file('file')->getRealPath());

        $request->merge(['markers' => $markers]);

        return $this->storeInBulk($request, $map);
    }

    /**
     * Show the locations for a given marker
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function indexLocations(Request $request, Map $map, Marker $marker)
    {
        $this->authorize('show', [$marker, $map, $request->input('map_token')]);

        // Start a query builder
        $query = $marker->locations();

        $data = $query->get();

        // If the request asks for "positional_data" then we need to include the inbound_course and outbound_course and groundspeed. These are computed attributes, not part of the SQL query;

        if ($request->input('computed_data')) {
            // We need to append the computed attributes to the collection
            $data->append(['inbound_course', 'groundspeed']);
        }

        // Finally, return the results
        return $data;
    }

    /**
     * Update the specified resource in storage.
     *
     * @todo deprecate adding a new location at this endpoint. Use storeLocation instead
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Map $map, Marker $marker)
    {
        $validated_data = $request->validate([
            'description' => ['nullable', 'string', 'max:191', new \App\Rules\NotContainsString()],
            'is_spam' => 'nullable|boolean',
        ]);

        if (isset($validated_data['is_spam'])) {
            $this->authorize('markAsSpam', [$marker, $request->input('map_token')]);
            $marker->is_spam = $validated_data['is_spam'];
            $marker->save();

            return $marker;
        } else {
            $this->authorize('update', [$marker, $request->input('map_token')]);
        }

        $marker->update($validated_data);

        // If the request has lat or lng, then we need to add a new location
        if ($request->input('lat') || $request->input('lng')) {
            // Pass the request to storeLocation
            return $this->storeLocation($request, $map, $marker);
        }

        return $marker->refresh();
    }

    /**
     * Add a new location to a marker
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Map $map
     * @param \App\Models\Marker $marker
     * @return void
     */
    public function storeLocation(Request $request, Map $map, Marker $marker)
    {
        $this->authorize('update', [$marker, $request->input('map_token')]);

        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'zoom' => 'nullable|numeric|between:0,20',
            'elevation' => 'nullable|numeric|between:-100000,100000',
            'heading' => 'nullable|numeric|between:0,360',
            'pitch' => 'nullable|numeric|between:-90,90',
            'roll' => 'nullable|numeric|between:-180,180',
            'speed' => 'nullable|numeric|between:0,100000',
        ]);

        $point = new Point($request->lat, $request->lng);

        $marker->currentLocation()->create([
            'location' => $point,
            'elevation' => $request->input('elevation'),
            'zoom' => $request->input('zoom'),
            'heading' => $request->input('heading'),
            'pitch' => $request->input('pitch'),
            'roll' => $request->input('roll'),
            'speed' => $request->input('speed'),
        ]);

        return $marker->refresh();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Map $map, Marker $marker)
    {
        $this->authorize('forceDelete', [$marker, $request->input('map_token')]);
        $marker->delete();
        return response()->json(['success' => true]);
    }

    private function validateCreate(Request $request, $marker, Map $map, Point $point)
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
