<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\Map;
use Carbon\Carbon;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Http\Request;
use Validator;

class IncidentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Map $map)
    {
        $this->authorize('index', [Incident::class, $map, $request->input('map_token')]);

        return $map->incidents->load('category');
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
    public function store(Request $request, Map $map)
    {
        //return $request->input("map_token");
        $this->authorize('create', [Incident::class, $map, $request->input('map_token')]);

        $request->merge(['user_id' => $request->user('api')->id ?? null]);
        if ($request->input('category') < 1) {
            $request->request->remove('category');
        }

        $request->validate([
            'category' => 'required_without:category_name|exists:categories,id',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'category_name' => ['required_without:category', 'min:3', 'max:32', new \App\Rules\NotContainsString],
            'user_id' => 'nullable',
        ]);

        $point = new Point($request->lng, $request->lat);

        if (! $request->input('category')) {
            $category = \App\Models\Category::firstOrCreate(
                ['slug' => str_slug($request->input('category_name'))],
                ['name' => $request->input('category_name'), 'icon' => '/images/marker-01.svg']
            );
            $request->merge(['category' => $category->id]);
        }

        Validator::make(
            ['point' => $point],
            ['point' => ['required', new \App\Rules\UniqueInRadius(15, $map->id, $request->input('category'))]]
        )->validate();

        $token = str_random(32);

        $result = new Incident(
            [
                'category_id' => $request->input('category'),
                'user_id' => $request->input('user_id'),
                'token' => $token,
                'map_id' => $map->id,
            ]
        );
        $result->location = $point;

        if ($map->options && $map->options['default_expiration_time']) {
            $result->expires_at = Carbon::now()->addMinutes($map->options['default_expiration_time'])->toDateTimeString();
        } else {
            $result->expires_at = null;
        }

        $result->save();

        broadcast(new \App\Events\IncidentCreated($result))->toOthers();

        return $result->makeVisible(['token'])->load('category');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Incident $qr)
    {
        return false;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Map $map, Incident $incident)
    {
        $this->authorize('forceDelete', $incident);
        $incident->delete();
    }
}
