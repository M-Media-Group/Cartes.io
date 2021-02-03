@extends('layouts.embed', ['index_and_googlebots' => $map->privacy == 'public' ? true : false])

@section('title', $map->title ?? "Untitled map")
@section('meta_description', $map->description ?? "No map description")
@section('meta_image', config('app.url').'/images/map.png')

@section('above_container')

@if( request()->get('type') == 'card' )
    <map-card-component :map="{{$map}}" :is_minimal="true" style="height:100%;max-width: 100%;"></map-card-component>
@else
    <map-component map_id="{{$map->uuid}}" style="height: 100vh;" users_can_create_markers="{{$map->users_can_create_markers}}" :map_categories="{{$map->categories}}"></map-component>
@endif

@endsection
