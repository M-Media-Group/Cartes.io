<?php

namespace App\Http\Controllers;

use App\Map;
use Feeds;
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
        if ($request->is('api*')) {
            return Map::where('privacy', 'public')->get();
        } else {
            return view('map');
        }
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
        // $this->authorize('create', Category::class);
        $validatedData = $request->validate([
            'title' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'privacy' => 'nullable|in:public,unlisted,private',
            'users_can_create_incidents' => 'nullable|in:yes,only_logged_in,no',
        ]);

        // // $image_path = $request->file('icon')->store('categories');
        $uuid = (string) Uuid::generate(4);
        $token = str_random(32);

        $result = new Map(
            [
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'slug' => str_slug($uuid),
                'uuid' => $uuid,
                'token' => $token,
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
     * @param  \App\Map  $map
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Map $map)
    {
        $feed = Feeds::make([
            'https://www.reddit.com/r/breakingnews/.rss', 'https://www.reddit.com/r/news/.rss', 'http://feeds.bbci.co.uk/news/world/rss.xml', 'https://news.google.com/rss/topics/CAAqJggKIiBDQkFTRWdvSUwyMHZNRGx1YlY4U0FtVnVHZ0pWVXlnQVAB?hl=en-US&gl=US&ceid=US:en',
        ]);
        $data = [
            'title' => $feed->get_title(),
            'permalink' => $feed->get_permalink(),
            'items' => $feed->get_items(),
            'token' => $request->session()->get('token'),
            'map' => $map,
        ];

        return View::make('map', $data);

        return $map;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Map  $map
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
     * @param  \App\Map  $map
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Map $map)
    {
        $validatedData = $request->validate([
            'map_id' => 'required|exists:maps,uuid',
            'token' => 'required|exists:maps,token',

            'title' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:maps,slug,'.$map->id,
            'description' => 'nullable|string',
            'privacy' => 'nullable|in:public,unlisted,private',
            'users_can_create_incidents' => 'nullable|in:yes,only_logged_in,no',

        ]);

        $map->update($validatedData);

        return $map;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Map  $map
     * @return \Illuminate\Http\Response
     */
    public function destroy(Map $map)
    {
        //
    }
}
