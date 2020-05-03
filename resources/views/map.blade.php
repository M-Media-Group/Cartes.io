@extends('layouts.clean', ['index_and_googlebots' => $map->privacy == 'public' ? true : false])


@section('title', $map->title)
@section('meta_description', $map->description)
@section('meta_image', config('app.url').'/images/map.jpg')



@section('above_container')
    <map-component map_id="{{$map->uuid}}" map_token="{{$token}}" style="height: 79vh;"></map-component>
@endsection
@section('content')
<h1 style="display: none;">{{config('app.name')}}</h1>

	    <map-details-component map_id="{{$map->uuid}}" map_token="{{$token}}" v-bind:map="{{$map}}"></map-details-component>


{{-- <button class="btn btn-primary mb-3" onclick="mymap.locate({setView: true, maxZoom: 18, watch: false});">Find my location on the map</button> --}}

@endsection
@section('footer_scripts')

@endsection
