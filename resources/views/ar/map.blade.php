@extends('layouts.clean', ['index_and_googlebots' => $map->privacy == 'public' ? true : false])

@section('title', $map->title ?? 'Untitled map')
@section('meta_description', $map->description ?? 'No map description')
@section('meta_image', config('app.url') . '/images/map.png')

@section('header_scripts')
    <script src="https://aframe.io/releases/1.0.4/aframe.min.js"></script>
    <script src="https://unpkg.com/aframe-look-at-component@0.8.0/dist/aframe-look-at-component.min.js"></script>
    <script src="https://raw.githack.com/AR-js-org/AR.js/master/aframe/build/aframe-ar-nft.js"></script>
@endsection

@section('above_container')
    <a-scene vr-mode-ui="enabled: false" arjs="sourceType: webcam; videoTexture: true; debugUIEnabled: false;">
        @foreach ($map->markers as $marker)
            <a-text value="{{ $marker->category->name }}" look-at="[gps-camera]" scale="70 70 70"
                gps-entity-place="latitude: {{ $marker->x }}; longitude: {{ $marker->y }};">
            </a-text>
        @endforeach
        <a-camera gps-camera rotation-reader>
        </a-camera>
    </a-scene>
@endsection
