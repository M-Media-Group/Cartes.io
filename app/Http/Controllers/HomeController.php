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
        //$this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        //return redirect('/');

        // $categories = $request->user()->seenCategories()->groupBy('category_id', 'category_views.user_id')->orderByRaw('MAX(category_views.created_at) DESC')->get();
        // $posts = $request->user()->seenPosts()->groupBy('post_id', 'post_views.user_id')->orderByRaw('MAX(post_views.created_at) DESC')->get();

        return view('home');
    }
}
