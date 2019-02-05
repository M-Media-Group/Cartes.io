@extends('layouts.app')

@section('title', 'A locals guide to '.config('blog.area_name'))
@section('meta_description', "Read about ".config('blog.area_name')." in the South of France (French Riviera), things to do, sights to see, and places to visit within - all from a locals perspective!")

@section('content')
	<h1>Posts</h1>
	@forelse($posts->sortByDesc('rank') as $post)
		<a href="/posts/{{$post->slug}}" title="{{ $post->title }}">
		    <img src="{{$post->header_image}}" class="rounded img-thumbnail mb-2" alt="{{ $post->title }}" >
		    <h2>{{ $post->title }}</h2>
		    <p class="text-muted">{{ $post->excerpt }}</p>
		    <p class="text-muted">
		    	@foreach($post->categories as $category)
			        <a href="/categories/{{$category->slug}}"><img class="rounded img-thumbnail mr-1" height="30" width="30" src="{{$category->icon}}" alt="{{$category->name}}">{{$category->name}}</a>
			    @endforeach
		    </p>
		    <hr>
		</a>
	@empty
		<h2>We couldn't find any posts matching your search term</h2>
	    <hr>
    @endforelse
    {{$posts->links()}}
@endsection
