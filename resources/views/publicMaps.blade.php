@extends('layouts.clean')

@section('title', 'Live maps for everyone and everything')
@section('meta_description', 'Create free anonymous or public maps without even having to sign up.')
@section('meta_image', config('app.url').'/images/map.png')

@section('above_container')

    <public-map-container-component ></public-map-container-component>
@endsection
