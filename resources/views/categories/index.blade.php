@extends('layouts.app')

@section('title', 'Categories')

@section('content')
	<h1 class="mt-3">Categories</h1>
	@foreach($categories as $category)
		<a href="/categories/{{$category->slug}}" class="row">
		    <img src="{{$category->icon}}" class="rounded img-thumbnail col-6" alt="{{ $category->name }}" >
		    <h2 class="col-6">{{ $category->name }}</h2>
		</a>
		<hr>
    @endforeach
    {{$categories->links()}}
@endsection
