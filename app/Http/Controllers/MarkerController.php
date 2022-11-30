<?php

namespace App\Http\Controllers;

use App\Models\Map;
use App\Models\Marker;
use App\Models\MarkerLocation;
use Carbon\Carbon;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialExpression;
use Grimzy\LaravelMysqlSpatial\Types\Point;
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
        return Marker::where('is_spam', false)
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
        $data = $data->with('category')->get();

        return $data;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Map $map)
    {
        $this->authorize('create', [Marker::class, $map, $request->input('map_token')]);

        $request->merge(['user_id' => optional($request->user())->id]);

        if ($request->input('category') < 1) {
            $request->request->remove('category');
        }

        $request->validate([
            'category' => 'required_without:category_name|exists:categories,id',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'description' => ['nullable', 'string', 'max:191', new \App\Rules\NotContainsString()],
            'category_name' => ['required_without:category', 'min:3', 'max:32', new \App\Rules\NotContainsString()],
            'user_id' => 'nullable|exists:users,id',
            'link' => [Rule::requiredIf(optional($map->options)['links'] === "required")],
            'elevation' => 'nullable|numeric|between:-100000,100000',
            "expires_at" => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        if (!$request->input('category')) {
            $category = \App\Models\Category::firstOrCreate(
                ['slug' => Str::slug($request->input('category_name'))],
                ['name' => $request->input('category_name'), 'icon' => '/images/marker-01.svg']
            );
            $request->merge(['category' => $category->id]);
        }

        $point = new Point($request->lat, $request->lng);

        $this->validateCreate($request, $request->input(), $map, $point);

        $result = new Marker(
            [
                'category_id' => $request->input('category'),
                'description' => clean($request->input('description')),
                'map_id' => $map->id,
                'link' => optional($map->options)['links'] && optional($map->options)['links'] !== "disabled" ? $request->input('link') : null,
            ]
        );

        $result->save();

        $result->currentLocation()->create([
            'location' => $point,
            'elevation' => $request->input('elevation'),
        ]);

        $result->refresh();

        return $result->makeVisible(['token'])->load('category');
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

        $bulkInsertId = Str::uuid();

        $request->merge(['user_id' => $request->user()->id]);

        $validated_data = $request->validate([
            'markers' => 'required|array|min:1',
            'markers.*.category' => 'required_without:markers.*.category_name|exists:categories,id',
            'markers.*.lat' => 'required|numeric|between:-90,90',
            'markers.*.lng' => 'required|numeric|between:-180,180',
            'markers.*.description' => ['nullable', 'string', 'max:191'],
            'markers.*.category_name' => ['required_without:markers.*.category', 'min:3', 'max:32', new \App\Rules\NotContainsString()],
            'user_id' => 'exists:users,id',
            'markers.*.created_at' => 'nullable',
            'markers.*.updated_at' => 'nullable',
            'markers.*.expires_at' => 'nullable',
            'markers.*.elevation' => 'nullable|numeric|between:-100000,100000',
            'markers.*.link' => [Rule::requiredIf(optional($map->options)['links'] === "required")]
        ]);

        $now = Carbon::now();

        $insertableData = [];

        foreach ($validated_data['markers'] as $index => $marker) {
            $marker['bulk_insert_id'] = $bulkInsertId;

            $point = new Point($marker['lat'], $marker['lng']);

            $this->validateCreate($request, $marker, $map, $point);

            $marker['location'] = new SpatialExpression($point);

            unset($marker['lat']);
            unset($marker['lng']);

            $marker['category_id'] = $marker['category'];
            unset($marker['category']);

            if (!$marker['category_id']) {
                $category = \App\Models\Category::firstOrCreate(
                    ['name' => $marker['category_name']]
                );
                $marker['category_id'] = $category->id;
                unset($marker['category_name']);
            }

            if (isset($marker['expires_at']) && !$marker['expires_at'] && $map->options && isset($map->options['default_expiration_time'])) {
                $marker['expires_at'] = $now->addMinutes($map->options['default_expiration_time'])->toDateTimeString();
            } elseif (!isset($marker['expires_at'])) {
                $marker['expires_at'] = null;
            } else {
                $marker['expires_at'] = Carbon::parse($marker['expires_at']);
            }

            $marker['link'] = optional($map->options)['links'] && optional($map->options)['links'] !== "disabled" ? ($marker['link'] ?? null) : null;

            // Check if created_at index exists
            if (isset($marker['created_at'])) {
                $marker['created_at'] = Carbon::parse($marker['created_at']);
            } else {
                $marker['created_at'] = $now->toDateTimeString();
            }

            // Check if updated_at index exists
            if (isset($marker['updated_at'])) {
                $marker['updated_at'] = Carbon::parse($marker['updated_at']);
            } else {
                $marker['updated_at'] = $now->toDateTimeString();
            }

            $marker['token'] = Str::random(32);
            $marker['map_id'] = $map->id;
            $marker['user_id'] = $validated_data['user_id'];

            $validated_data['markers'][$index] = $marker;

            // Unset data that should not be set
            unset($marker['elevation']);
            unset($marker['address']);
            unset($marker['current_location']);
            unset($marker['location']);

            $insertableData[] = $marker;
        }

        DB::beginTransaction();

        try {
            $result = Marker::insert($insertableData);
            $markerIds = Marker::where('bulk_insert_id', $bulkInsertId)->get();

            $positionData = [];
            $currentIteration = 0;

            foreach ($markerIds as $marker) {
                $positionData[] = [
                    'marker_id' => $marker->id,
                    'location' => $validated_data['markers'][$currentIteration]['location'],
                    'elevation' => optional($validated_data['markers'][$currentIteration])['elevation'],
                    'address' => optional($validated_data['markers'][$currentIteration])['address'],
                    'user_id' => $marker->user_id,
                    'created_at' => $marker->created_at,
                    'updated_at' => $marker->updated_at,
                ];
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
     * Update the specified resource in storage.
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

        return $marker;
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
