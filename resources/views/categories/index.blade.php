@extends('layouts.app')

@section('title', 'Visit Villefranche based on your interests')
@section('meta_description', "See the French Riviera city Villefranche sur Mer! Explore the bay, beaches, citadel, and more in this charming small city in tucked away in the South of France (Cote d'Azur).")

@section('content')
	<h1>Categories</h1>
	@foreach($categories as $category)
		<a href="/categories/{{$category->slug}}" class="row">
		    <img src="{{$category->icon}}" class="rounded img-thumbnail col-6" alt="{{ $category->name }}" >
		    <h2 class="col-6">{{ $category->name }}</h2>
		</a>
		<hr>
    @endforeach
    {{$categories->links()}}
@endsection
