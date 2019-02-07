@extends('layouts.clean')

@section('title', 'Map of '.config('blog.area_name'))
@section('meta_description', "Check out the interactive map of the South of France city ".config('blog.area_name')." showing bus stops, train stations, and car parkings.")
@section('meta_image', config('app.url').'/images/map.jpg')

@section('header_scripts')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.4.0/dist/leaflet.css" integrity="sha512-puBpdR0798OZvTTbP4A8Ix/l+A4dHDD0DGqYW6RQ+9jxkRFclaxxQb/SJAWZfWAkuyeQUytO7+7N4QKrDh+drA==" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.4.0/dist/leaflet.js" integrity="sha512-QVftwZFqvtRNi0ZyCtsznlKSWOStnDORoefr1enyq5mVL4tmKB3S/EnC3rRJcxCPavG10IcrVGSmPh6Qw5lwrg==" crossorigin=""></script>
@endsection

@section('above_container')
<div id="mapid" style="width: 100%; height: 69vh;"></div>

@endsection
@section('content')
<h1>Map of {{config('blog.area_name')}}</h1>
<p>You can control what you see on the map using the layers control (<span class="leaflet-control-layers-toggle d-inline-block" style="height:25px;"></span>) on the top right of the map.</p>
<div class="d-flex flex-column justify-content-start" style="display:none !important;">
    <div>
        <img src='/images/icons/bus.svg' class='rounded img-thumbnail' alt="Bus stop" style='height:35px;'> = Bus stop
    </div>
    <div>
        <img src='/images/icons/car.svg' class='rounded img-thumbnail' alt="Car park" style='height:35px;'> = Car park
    </div>
    <div>
        <img src='/images/icons/train.svg' class='rounded img-thumbnail' alt="Train station" style='height:35px;'> = Train station
    </div>
    <div class="mb-3">
        <img src='/images/icons/heart.svg' class='rounded img-thumbnail' alt="Valentine's Day" style='height:35px;'> = Valentine's Day event
    </div>
</div>
<p>The <img src='{{config('blog.logo_url')}}' alt="Logo" class='rounded img-thumbnail' style='height:25px;'> pins on the map represent <a href="https://explorevillefranche.com/posts/qr-codes-in-villefranche">QR codes</a> scattered throughout the city that you can scan to learn more about whatever you're looking at!</p>
<button class="btn btn-primary mb-3" onclick="mymap.locate({setView: true, maxZoom: 20, watch: true});">Find my location on the map</button>
@endsection
@section('footer_scripts')
<script src='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/Leaflet.fullscreen.min.js'></script>
<link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/leaflet.fullscreen.css' rel='stylesheet' />
<script>

    var mymap = L.map('mapid', {fullscreenControl: true}).setView([43.7040, 7.3111], 17);
// https://leaflet-extras.github.io/leaflet-providers/preview/
     L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager_labels_under/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
    subdomains: 'abcd',
    maxZoom: 19,
    minZoom: 11,
   //detectRetina:true
}).addTo(mymap);

    var myIcon = L.icon({
        iconUrl: "{{config('blog.logo_url')}}",
        iconSize: [20, 20],
        iconAnchor: [10, 10],
        popupAnchor: [0, 0]
    });
    @foreach(\App\QrCode::selectRaw('id, X(`location`) as x, Y(`location`) as y')->withCount('views')->get()->sortBy('views_count') as $qr)

        L.marker([{{$qr->x}}, {{$qr->y}}], {icon: myIcon}).addTo(mymap).bindPopup("<b>QR Code</b>@can('manage qr codes') <small>{{$qr->id}}</small><br/>{{$qr->views_count}} check-ins @endcan <br/><small><a href='https://explorevillefranche.com/posts/qr-codes-in-villefranche'>What's this?</a></small>");

    @endforeach

var popup = L.popup();

mymap.on('contextmenu', onMapClick);

mymap.on('locationfound', onLocationFound);

mymap.on('locationerror', onLocationError);
mymap.on('baselayerchange', onOverlayAdd);

mymap.on('popupopen', trackPopup);
mymap.on('zoomend', trackZoomChange);
mymap.on('moveend', trackMoveChange);

function onMapClick(e) {
    popup
        .setLatLng(e.latlng)
        .setContent("Clicked at " + e.latlng.toString())
        .openOn(mymap);
}

function onLocationFound(e) {
    var radius = e.accuracy / 2;

    L.marker(e.latlng).addTo(mymap)
        .bindPopup("You are within " + radius + " meters from this point").openPopup();

    L.circle(e.latlng, radius).addTo(mymap);

    dataLayer.push({'event': 'User location found'});
}

function onLocationError(e) {
    alert(e.message);
}

function onOverlayAdd(e){
    dataLayer.push({'event': 'Map layer change', 'value': e.name.replace(/<(?:.|\n)*?> /gm, '')});
}

function trackZoomChange(e){
    dataLayer.push({'event': 'Map zoom', 'value': mymap.getZoom()});
}

function trackMoveChange(e){
    dataLayer.push({'event': 'Map move', 'value': 'Center '+mymap.getCenter().toString()});
}

function trackPopup(e){
    if(e.popup._source && e.popup._source.feature) {
        dataLayer.push({'event': 'Map popup open', 'id': "Feature "+e.popup._source.feature.properties.full_id});
    } else if (e.popup._source) {
    dataLayer.push({'event': 'Map popup open', 'id': "QR Code "+e.popup._latlng.toString()});
    } else if (e.popup._latlng) {
        dataLayer.push({'event': 'Map popup open', 'id': "Position "+e.popup._latlng.toString()});
    }
}
</script>
<script src="{{ asset('js/mapdata.js') }}" defer></script>

@endsection
