<?php

namespace App\Http\Controllers;

use App\Helpers\MapImageGenerator;
use App\Http\Resources\MapResource;
use App\Models\Map;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class MapController extends Controller
{
    public function __construct()
    {
        $this->middleware('throttle:maps')->only(['store', 'update', 'destroy']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!$request->wantsJson()) {
            if (config('app.spa_url')) {
                return redirect(config('app.spa_url'));
            }
            return view('publicMaps');
        }

        // $this->authorize('index', [Map::class, $map, $request->input('map_token')]);
        //return \App\Models\Map::whereDoesntHave('markers')->delete();
        $request->validate([
            'ids' => 'nullable|array|between:1,100',
            'category_ids' => 'nullable|array|between:1,10',
            'withMine' => 'nullable|boolean',
            'orderBy' => 'nullable|string',
        ]);

        $query = Map::query();

        if ($request->input('ids')) {
            $query->where(function ($query) use ($request) {
                $query->whereIn('uuid', $request->input('ids'))->where('privacy', '!=', 'private');

                $query->when($request->input('withMine'), function ($query) use ($request) {
                    if (!$request->user()) {
                        return abort(401, 'You need to be authenticated to get your own maps.');
                    };
                    return $query->orWhere('user_id', $request->user()->id);
                });
            });
        } else {
            $query->when($request->input('withMine'), function ($query) use ($request) {
                if (!$request->user()) {
                    return abort(401, 'You need to be authenticated to get your own maps.');
                };
                return $query->where('user_id', $request->user()->id);
            }, function ($query) {
                return $query->public();
            });
        }

        $query->when($request->input('category_ids'), function ($query, $category_ids) {
            return $query->whereHas('categories', function ($q) use ($category_ids) {
                $q->whereIn('category_id', $category_ids);
            });
        });

        $query->orderBy($request->input('orderBy', 'created_at'), 'desc');

        return MapResource::collection($query->filterAndExpand()->parseQuery()->paginate());
    }

    /**
     * Search for maps
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:3|max:255',
        ]);
        return MapResource::collection(Map::search($request->input('q'))->where('privacy', 'public')->paginate());
    }

    /**
     * Return related maps for a given map
     *
     * @param \App\Models\Map $map
     * @return \Illuminate\Http\Response
     */
    public function related(Map $map)
    {
        $this->authorize('view', $map);
        return $map->related;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|MapResource
     */
    public function store(Request $request)
    {
        $this->authorize('create', Map::class);

        $validatedData = $request->validate([
            'title' => 'nullable|string|max:191',
            'slug' => 'nullable|string|max:255|unique:maps,slug',
            'description' => 'nullable|string',
            'privacy' => 'nullable|in:public,unlisted,private',
            'users_can_create_markers' => 'nullable|in:yes,only_logged_in,no',
        ]);

        $result = new Map($validatedData);

        $result->save();

        $result->makeVisible(['token']);

        if ($request->wantsJson()) {
            return new MapResource($result);
        } else {
            return redirect('/maps/' . $result->slug)->with('token', $result->token);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Map  $map
     * @return \Illuminate\Http\RedirectResponse|MapResource
     */
    public function show(Request $request, $uuid)
    {
        $map = Map::where('uuid', $uuid)->expand()->firstOrFail();

        $this->authorize('view', $map);

        $map->load(['categories', 'publicContributors', 'user']);

        if ($request->wantsJson()) {
            return new MapResource($map);
        }

        // Redirect away to the app.cartes.io version
        if ($map->shouldUseNewApp) {
            return redirect(config('app.spa_url') . '/maps/' . $map->slug);
        }

        $data = [
            'token' => $request->session()->get('token'),
            'map' => $map,
        ];

        return View::make('map', $data);
    }

    /**
     * Return the static image for the map
     *
     * @param \App\Models\Map $map
     * @return \Illuminate\Http\Response
     */
    public function getStaticImage(Map $map)
    {
        $this->authorize('view', $map);

        $mapImageGenerator = new MapImageGenerator();

        // Validate the data, we can optionally pass in a width, height, zoom and responseType (base64 or png)
        $validatedData = request()->validate([
            'width' => 'nullable|in:' . implode(',', $mapImageGenerator->allowedWidths),
            'height' => 'nullable|in:' . implode(',', $mapImageGenerator->allowedHeights),
            'zoom' => 'nullable|numeric|between:2,19',
            'responseType' => 'nullable|in:base64,png',
        ]);

        $mapImageGenerator->updateImageDimensions($validatedData['width'] ?? null, $validatedData['height'] ?? null);

        $mapImageGenerator->updateImageCenter(null, null, $validatedData['zoom'] ?? null);

        $mapImageGenerator->updateResponseType($validatedData['responseType'] ?? null);

        $image = $mapImageGenerator->getOrGenerateForMap($map);


        return response($image, 200, $mapImageGenerator->getAllHeaders());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Map  $map
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Map $map)
    {
        $this->authorize('update', $map);

        $validatedData = $request->validate([
            'title' => 'nullable|string|max:191',
            'slug' => 'nullable|string|max:255|unique:maps,slug,' . $map->id,
            'description' => 'nullable|string',
            'privacy' => 'nullable|in:public,unlisted,private',
            'users_can_create_markers' => 'nullable|in:yes,only_logged_in,no',
            'options.default_expiration_time' => 'nullable|numeric|between:1,525600',
            'options.limit_to_geographical_body_type' => 'nullable|in:land,water,no',
            'options.links' => 'nullable|in:required,optional,disabled',
        ]);

        // If the privacy is set to private, we need to ensure that there is a currently logged in user
        if (!$map->user_id && $request->input('privacy') === 'private' && !$request->user()) {
            return response()->json(['error' => 'You must be logged in to make this map private'], 401);
        } elseif (!$map->user_id && $request->input('privacy') === 'private') {
            $map->user_id = $request->user()->id;
        }

        $map->update($validatedData);

        return $map;
    }

    /**
     * Attach the map to the current user
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Map $map
     * @return \Illuminate\Http\Response
     */
    public function claim(Request $request, Map $map)
    {
        $this->authorize('update', $map);
        $map->user_id = $request->user()->id;
        $map->save();
        return $map;
    }

    /**
     * Detach the user associated with the map
     *
     * @param \App\Models\Map $map
     * @return \Illuminate\Http\Response
     */
    public function unClaim(Map $map)
    {
        $this->authorize('update', $map);
        $map->user_id = null;
        $map->save();
        return $map;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Map  $map
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, Map $map)
    {
        $this->authorize('forceDelete', $map);
        $map->delete();
        return response()->json(['success' => true]);
    }
}
