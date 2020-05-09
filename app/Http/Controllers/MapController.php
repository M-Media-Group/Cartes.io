<?php

namespace App\Http\Controllers;

use App\Models\Map;
use Illuminate\Http\Request;
use Uuid;
use View;

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
        //return \App\Models\Map::whereDoesntHave('incidents')->delete();
        $request->validate([
            'ids' => 'nullable|array|between:1,100',
            'category_ids' => 'nullable|array|between:1,10',
            'orderBy' => 'nullable|string',
        ]);

        $category_ids = $request->input('category_ids');
        $query = Map::with('categories')->withCount('incidents');

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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
            'users_can_create_incidents' => 'nullable|in:yes,only_logged_in,no',
        ]);

        $uuid = (string) Uuid::generate(4);

        $result = new Map(
            [
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'slug' => $request->input('slug', str_slug($uuid)),
                'uuid' => $uuid,
                'token' => str_random(32),
                'privacy' => $request->input('privacy', 'unlisted'),
                'users_can_create_incidents' => $request->input('users_can_create_incidents', 'only_logged_in'),
                'user_id' => $request->user() ? $request->user()->id : null,
            ]
        );
        $result->save();
        $result->makeVisible(['token']);

        if ($request->is('api*')) {
            return $result;
        } else {
            return redirect('/maps/'.$result->slug)->with('token', $result->token);
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
            'slug' => 'nullable|string|max:255|unique:maps,slug,'.$map->id,
            'description' => 'nullable|string',
            'privacy' => 'nullable|in:public,unlisted,private',
            'users_can_create_incidents' => 'nullable|in:yes,only_logged_in,no',
            'options.default_expiration_time' => 'nullable|numeric|between:1,525600',

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
