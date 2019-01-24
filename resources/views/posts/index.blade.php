@extends('layouts.app')

@section('title', 'Posts')
@section('meta_description', 'Posts about '.config('app.name'))

@section('content')
	<h1 class="mt-3">Posts</h1>
	@foreach($posts as $post)
		<a href="/posts/{{$post->slug}}" title="{{ $post->title }}">
		    <img src="{{$post->header_image}}" class="rounded img-thumbnail" alt="{{ $post->title }}" >
		    <h2>{{ $post->title }}</h2>
		    <p class="text-muted">{{ $post->excerpt }}</p>
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
