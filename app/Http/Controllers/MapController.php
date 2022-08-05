<?php

namespace App\Http\Controllers;

use App\Models\Map;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class MapController extends Controller
{

    // Constructor with middlewares
    public function __construct()
    {
        $this->middleware('throttle:maps')->only('store');
        $this->middleware('throttle:maps')->only(['update', 'destroy']);
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
            'orderBy' => 'nullable|string',
        ]);

        $query = Map::with('categories')->withCount('markers');

        if ($request->input('ids')) {
            $query->whereIn('uuid', $request->input('ids'))->where('privacy', '!=', 'private');
        } else {
            $query->where('privacy', 'public');
        }

        $query->when($request->input('category_ids'), function ($query, $category_ids) {
            return $query->whereHas('categories', function ($q) use ($category_ids) {
                $q->whereIn('category_id', $category_ids);
            });
        });

        $query->when($request->input('withMine'), function ($query) use ($request) {
            if (!$request->user()) {
                return abort(401, 'You need to be authenticated to get your own maps.');
            };
            return $query->orWhere('user_id', $request->user()->id);
        });

        $query->orderBy($request->input('orderBy', 'created_at'), 'desc');

        return $query->paginate();
    }

    public function related(Request $request, Map $map)
    {
        $this->authorize('view', $map);
        return $map->related;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', Map::class);
        $validatedData = $request->validate([
            'title' => 'nullable|string|max:191',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'privacy' => 'nullable|in:public,unlisted,private',
            'users_can_create_markers' => 'nullable|in:yes,only_logged_in,no',
        ]);

        $result = new Map(
            [
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'slug' => $request->input('slug'),
                'privacy' => $request->input('privacy'),
                'users_can_create_markers' => $request->input('users_can_create_markers'),
            ]
        );

        $result->save();
        $result->makeVisible(['token']);

        if ($request->wantsJson()) {
            return response()->json($result);
        } else {
            return redirect('/maps/' . $result->slug)->with('token', $result->token);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Map  $map
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Map $map)
    {
        $this->authorize('view', $map);

        $map->load('categories');

        if ($request->wantsJson()) {
            return $map;
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
     * Display the specified resource.
     *
     * @param  \App\Models\Map  $map
     * @return \Illuminate\Http\Response
     */
    public function showEmbed(Request $request, Map $map)
    {
        $this->authorize('view', $map);

        $data = [
            'map' => $map->load('categories'),
        ];

        // Redirect away to the app.cartes.io version
        if ($map->shouldUseNewApp) {
            return redirect(config('app.spa_url') . '/maps/' . $map->slug . '/embed');
        }

        return View::make('embeds/map', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Map  $map
     * @return \Illuminate\Http\Response
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Map  $map
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Map $map)
    {
        $this->authorize('forceDelete', $map);
        $map->delete();
        return response()->json(['success' => true]);
    }
}
