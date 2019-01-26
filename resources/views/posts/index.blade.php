@extends('layouts.app')

@section('title', 'A locals guide to Villefranche sur Mer')

@section('content')
	<h1 class="mt-3">Posts</h1>
	@foreach($posts->sortByDesc('views_count') as $post)
		<a href="/posts/{{$post->slug}}" title="{{ $post->title }}">
		    <img src="{{$post->header_image}}" class="rounded img-thumbnail mb-2" alt="{{ $post->title }}" >
		    <h2>{{ $post->title }}</h2>
		    <p class="text-muted">{{ $post->excerpt }} | {{$post->views_count}}</p>
		    <p class="text-muted">
		    	@foreach($post->categories as $category)
			        <a href="/categories/{{$category->slug}}"><img class="rounded img-thumbnail mr-1" height="30" width="30" src="{{$category->icon}}" alt="{{$category->name}}">{{$category->name}}</a>
			    @endforeach
		    </p>
		    <hr>
		</a>
    @endforeach
    {{$posts->links()}}
@endsection
