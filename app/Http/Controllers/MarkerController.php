<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMarkerRequest;
use App\Http\Resources\MarkerGeoJsonCollection;
use App\Models\Map;
use App\Models\Marker;
use App\Parsers\Files\GeoJSONParser;
use App\Parsers\Files\GPXParser;
use Carbon\Carbon;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

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

        Marker::validateCreate($request, $request->input(), $map, $point);

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

        try {
            $validated_data = Marker::validateRequestForBulkInsert($request, $map);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        }

        return Marker::bulkInsertWithLocations($validated_data['markers'], $map);
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
        $this->authorize('uploadFromFile', [Marker::class, $map, $request->input('map_token')]);

        // Get the uploaded file type for debug
        $fileMimeType = $request->file('file')->getMimeType();
        $fileExtension = $request->file('file')->extension();
        $clientExtension = $request->file('file')->getClientOriginalExtension();
        $clientMimeType = $request->file('file')->getClientMimeType();

        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:gpx,geojson,json,xml',
                'mimetypes:application/json,application/gpx,application/gpx+xml,text/xml,application/geo+json',
                // Max 1MB
                'max:1024',
            ],
        ]);

        if (Str::contains($fileMimeType, 'gpx') || Str::contains($clientMimeType, 'gpx') || $fileExtension === 'gpx' || $clientExtension === 'gpx') {
            $parser = new GPXParser();
        } elseif (Str::contains($fileMimeType, 'json') || Str::contains($clientMimeType, 'json')) {
            $parser = new GeoJSONParser();
        } else {
            return response()->json(['error' => 'File type not supported'], 422);
        }

        $markers = $parser->parseFile($request->file('file')->getRealPath())['markers'];

        $request->merge(['markers' => $markers]);

        try {
            $validated_data = Marker::validateRequestForBulkInsert($request, $map);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        }

        return Marker::bulkInsertWithLocations($validated_data['markers'], $map);
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
