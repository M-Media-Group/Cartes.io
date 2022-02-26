<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware(['verified', 'optimizeImages'])->except(['index', 'show']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search_query = $request->input('query');
        if ($request->is('api*')) {
            return Category::withCount('views')
                ->when($search_query, function ($query, $search_query) {
                    return $query->where('name', 'like', "%{$search_query}%");
                })
                ->orderBy($request->input('orderBy', 'created_at'), 'desc')
                ->get();
        } else {
            return view('categories.index', ['categories' => Category::withCount('views')->simplePaginate(7)]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create', Category::class);

        return view('categories.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', Category::class);
        $validatedData = $request->validate([
            'name' => 'required|unique:categories|min:3|max:255',
            'icon' => 'required|image',
        ]);

        $image_path = $request->file('icon')->store('categories');

        $result = new Category(
            [
                'name' => $request->input('name'),
                'slug' => Str::slug($request->input('name')),
                'icon' => Storage::url($image_path),
            ]
        );
        $result->save();

        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $slug)
    {
        $category = Category::where('slug', $slug)->with('markers')->firstOrFail();
        $this->authorize('view', $category);
        if (!$request->user()) {
            $user_id = null;
        } else {
            $user_id = $request->user()->id;
        }
        \App\Models\CategoryView::create(
            [
                'category_id' => $category->id,
                'user_id' => $user_id,
                'ip' => $request->ip(),
            ]
        );

        if ($request->is('api*')) {
            return $category;
        } else {
            return view('categories.show', ['category' => $category]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit($slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $this->authorize('update', $category);

        return view('categories.edit', ['category' => $category]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        $this->authorize('update', $category);
        $validatedData = $request->validate([
            'name' => 'required|unique:categories|min:3|max:255',
        ]);

        $category->update(
            [
                'name' => $request->input('name'),
                'slug' => Str::slug($request->input('name')),
            ]
        );

        return redirect('/categories/' . Str::slug($request->input('name')));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);
    }
}
