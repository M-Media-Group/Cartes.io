<?php

namespace App\Http\Controllers;

use App\Incident;
use DB;
use Illuminate\Http\Request;

class IncidentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Incident::get();
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
        //$this->authorize('create', Incident::class);
        $validatedData = $request->validate([
            'category' => 'required|exists:categories,id',
            'lat' => 'required',
            'lng' => 'required',
        ]);

        $location = DB::raw("(GeomFromText('POINT(" . $request->lat . " " . $request->lng . ")'))");

        $result = new Incident(
            [
                'location' => $location,
                'category_id' => $request->input('category'),
                'user_id' => $request->user()->id,
            ]
        );
        $result->save();

        return back();
        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Incident $qr)
    {
        if (!$request->user()) {
            $user_id = null;
        } else {
            $user_id = $request->user()->id;
        }
        \App\IncidentView::create(
            [
                "incident_id" => $qr->id,
                "user_id" => $user_id,
                "ip" => $request->ip(),
            ]
        );
        $query_parameters = ['utm_source' => 'real_world', 'utm_medium' => 'incident', 'utm_campaign' => 'website_incidents', 'utm_content' => $qr->id];
        return redirect($qr->redirect_to . '?' . http_build_query($query_parameters));
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
    public function destroy($id)
    {
        //
    }
}
