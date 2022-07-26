<?php

namespace App\Http\Controllers;

use App\Models\Map;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class MapController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

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
        $query->orderBy($request->input('orderBy', 'created_at'), 'desc');

        return $query->paginate();
    }

    public function related(Request $request, Map $map)
    {
        $this->authorize('view', $map);
        return $map->relatedMaps;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return redirect('/');
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

        if ($request->is('api*')) {
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
        if ($request->is('api*')) {
            return $map;
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

        return View::make('embeds/map', $data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Map  $map
     * @return \Illuminate\Http\Response
     */
    public function showAr(Request $request, Map $map)
    {
        $this->authorize('view', $map);

        $data = [
            'map' => $map->load('categories'),
        ];

        return View::make('ar/map', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Map  $map
     * @return \Illuminate\Http\Response
     */
    public function edit(Map $map)
    {
        //
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
    }
}
