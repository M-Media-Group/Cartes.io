<?php

namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['verified', 'optimizeImages'])->except(['index', 'show']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = Post::published()->with('categories')->orderBy('published_at', 'DESC')->simplePaginate(7);
        //return $posts;

        return view('posts.index', ['posts' => $posts]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $this->authorize('create', Post::class);
        return view('posts.create', ['posts' => Post::with('categories')->simplePaginate(7), 'categories' => \App\Category::get()]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', Post::class);
        $validatedData = $request->validate([
            'title' => 'required|unique:posts|min:5|max:255',
            'body_markdown' => 'required|min:200',
            'excerpt' => 'required|min:25|max:255',
            'header_image' => 'required|image|dimensions:min_width=960,min_height=300,max_width=2560|max:512',
        ]);

        $image_path = $request->file('header_image')->store('header_images');

        $result = new Post(
            [
                'title' => $request->input('title'),
                'body_markdown' => $request->input('body_markdown'),
                'excerpt' => $request->input('excerpt'),
                'slug' => str_slug($request->input('title')),
                'header_image' => Storage::url($image_path),
                'user_id' => $request->user()->id,
                'published_at' => now(),

            ]
        );
        $result->save();

        $result->categories()->attach($request->input('category_id'));

        return $result->load('categories');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $slug)
    {
        $post = \App\Post::where('slug', $slug)->with('user', 'categories')->firstOrFail();
        if (!$request->user()) {
            $user_id = null;
        } else {
            $user_id = $request->user()->id;
        }
        \App\PostView::create(
            [
                "post_id" => $post->id,
                "user_id" => $user_id,
                "ip" => $request->ip(),
            ]
        );
        return view('posts.show', ['post' => $post]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function edit($slug)
    {
        $post = \App\Post::where('slug', $slug)->firstOrFail();
        $this->authorize('update', $post);
        $categories = \App\Category::get();
        return view('posts.edit', compact('post', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);
        $validatedData = $request->validate([
            'title' => 'required|unique:posts,title,' . $post->id . '|min:5|max:255',
            'body_markdown' => 'required|min:10',
            'excerpt' => 'required|min:10|max:255',
            'category_id' => 'required',
        ]);

        $post->update(
            [
                'title' => $request->input('title'),
                'body_markdown' => $request->input('body_markdown'),
                'excerpt' => $request->input('excerpt'),
                'slug' => str_slug($request->input('title')),
            ]
        );

        $post->categories()->sync($request->input('category_id'));

        return redirect('/posts/' . str_slug($request->input('title')));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);
        $post->categories()->detach();
        $post->views()->delete();
        $post->delete();
        return redirect('/posts');
    }
}
