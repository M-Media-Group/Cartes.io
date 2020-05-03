<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\Map;
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
        if ($request->is('api*')) {
            return Incident::with('category')->where('map_id', $map->id)->get();
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
    public function store(Request $request, Map $map)
    {
        //return $request->input("map_token");
        $this->authorize('create', [Incident::class, $map, $request->input('map_token')]);

        //return Auth::id();

        $request->merge(['user_id' => $request->user('api')->id ?? null]);
        if ($request->input('category') < 1) {
            $request->request->remove('category');
        }
        $request->validate([
            'category' => 'required_without:category_name|exists:categories,id',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-90,90',
            'category_name' => ['required_without:category', 'min:3', 'max:32', new \App\Rules\NotContainsString],
            'user_id' => 'nullable',
            //'map_id' => ['required_without:user_id', 'numeric', 'exists:maps,uuid'],
        ]);

        //$location = DB::raw("(GeomFromText('POINT(" . $request->lat . ' ' . $request->lng . ")'))");
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
            ['point' => ['required', new \App\Rules\UniqueInRadius(15, null, $request->input('category'))]]
        )->validate();

        //return $point->getLat();

        $token = str_random(32);

        $result = new Incident(
            [
                // 'location' => $location,
                'category_id' => $request->input('category'),
                'user_id' => $request->input('user_id'),
                'token' => $token,
                'map_id' => $map->id,
            ]
        );
        $result->location = $point;
        $result->save();

        broadcast(new \App\Events\IncidentCreated($result));

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
        if (! $request->user()) {
            $user_id = null;
        } else {
            $user_id = $request->user()->id;
        }
        \App\Models\IncidentView::create(
            [
                'incident_id' => $qr->id,
                'user_id' => $user_id,
                'ip' => $request->ip(),
            ]
        );
        $query_parameters = ['utm_source' => 'real_world', 'utm_medium' => 'incident', 'utm_campaign' => 'website_incidents', 'utm_content' => $qr->id];

        return redirect($qr->redirect_to.'?'.http_build_query($query_parameters));
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
        //return $incident->token;
        $this->authorize('forceDelete', $incident);
        $incident->delete();
    }
}
