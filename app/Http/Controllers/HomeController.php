<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        //return $request->user()->seenPosts()->latest()->groupBy('post_id', 'post_views.user_id')->get();
        return view('home', ['posts' => $request->user()->seenPosts()->groupBy('post_id', 'post_views.user_id')->orderByRaw('MAX(post_views.created_at) DESC')->get()]);
        //return view('home');
    }
}
