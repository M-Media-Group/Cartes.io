<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        if (config('app.spa_url') && !$request->user()) {
            return redirect(config('app.spa_url'));
        }
        return view('home');
    }
}
