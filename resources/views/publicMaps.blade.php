@extends('layouts.clean', ['superclean' => true])

@section('title', 'Live maps for everyone and everything')
@section('meta_description', 'Create free anonymous or public maps without even having to sign up.')
@section('meta_image', config('app.url').'/images/map.png')

@section('above_container')

    <public-map-container-component ></public-map-container-component>
    <a class="navbar-brand" href="{{ url('/') }}" style="position: fixed; right: 1.5rem; top:1.5rem; z-index: 10000">
      {{ config('app.name') }} <span class="text-muted small d-none">Beta</span>
    </a>
@endsection
