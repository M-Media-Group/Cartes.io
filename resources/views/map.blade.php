@extends('layouts.clean')

@section('title', 'Incident Report')
@section('meta_description', "Interactive map of incidents that may be dangerous to activists, human rights defenders, aid workers, social workers, NGO staff, or journalists.")
@section('meta_image', config('app.url').'/images/map.jpg')

@section('header_scripts')
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.4.0/dist/leaflet.css" integrity="sha512-puBpdR0798OZvTTbP4A8Ix/l+A4dHDD0DGqYW6RQ+9jxkRFclaxxQb/SJAWZfWAkuyeQUytO7+7N4QKrDh+drA==" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.4.0/dist/leaflet.js" integrity="sha512-QVftwZFqvtRNi0ZyCtsznlKSWOStnDORoefr1enyq5mVL4tmKB3S/EnC3rRJcxCPavG10IcrVGSmPh6Qw5lwrg==" crossorigin=""></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet.locatecontrol/dist/L.Control.Locate.min.css" />

@endsection

@section('above_container')
<div id="mapid" style="width: 100%; height: 71vh;"></div>

@endsection
@section('content')
<h1 style="display: none;">{{config('app.name')}}</h1>
<p>Right click (or long-tap on mobile) on the map to report incidents that may be dangerous to activists, human rights defenders, aid workers, social workers, NGO staff, or journalists.</p>
<p>After 59 minutes, your report will automatically dissapear from the map.</p>
<p class="text-muted small">You're currently looking at: <span id='coordinates'>No incidents</span>.</p>

{{-- <button class="btn btn-primary mb-3" onclick="mymap.locate({setView: true, maxZoom: 18, watch: false});">Find my location on the map</button> --}}

@endsection
@section('footer_scripts')
<script src="https://code.jquery.com/jquery-3.3.1.min.js"
      integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
      crossorigin="anonymous">
</script>
<script src='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/Leaflet.fullscreen.min.js'></script>
<link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/leaflet.fullscreen.css' rel='stylesheet' />

<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>

<script src="https://cdn.jsdelivr.net/npm/leaflet.locatecontrol/dist/L.Control.Locate.min.js" charset="utf-8"></script>

<script>

    var mymap = L.map('mapid', {fullscreenControl: true}).setView([43.7040, 7.3111], 3);
    var markers = L.markerClusterGroup({zoomToBoundsOnClick: true, spiderfyOnMaxZoom: false, disableClusteringAtZoom: 17, chunkedLoading: true, maxClusterRadius: 30});

var lc = L.control.locate({
    position: 'topright',
    strings: {
        title: "Show me where I am, yo!"
    },
    locateOptions: {
        enableHighAccuracy: true,
        watch: true
    }
}).addTo(mymap);
// https://leaflet-extras.github.io/leaflet-providers/preview/
     L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager_labels_under/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
    subdomains: 'abcd',
    maxZoom: 19,
    minZoom: 2,
   //detectRetina:true
}).addTo(mymap);

    L.Control.geocoder({expand: 'click', position : 'topright', showResultIcons: true}).addTo(mymap);

    var myIcon = L.icon({
        iconUrl: "{{config('blog.logo_url')}}",
        iconSize: [20, 20],
        iconAnchor: [10, 10],
        popupAnchor: [0, 0]
    });

        @foreach(App\Category::with('incidents')->get() as $category)
        var geojson{{$category->id}} = L.geoJson(
                {
                    "type": "FeatureCollection",
                    "features": [
                    @foreach( $category->incidents as $incident )
                        { "type": "Feature", "properties": { "updated_at": "{{ $incident->updated_at->timestamp }}000", "created_at": "{{ $incident->created_at->timestamp }}000", "category": "{{ $category->name }}" }, "geometry": { "type": "Point", "coordinates": [{{$incident->y}}, {{$incident->x}}] } },
                    @endforeach()
                    ]
                }, {
            pointToLayer: function(geoJsonPoint, latlng) {
                return L.marker(latlng, { icon: L.icon({
                        iconUrl: "{{$category->icon}}",
                        iconSize: [null, 20],
                        iconAnchor: [10, 10],
                        popupAnchor: [0, 0]
                    })
                });
            },
            onEachFeature: function (feature, layer) {
                layer.bindPopup(function(layer) {
                    var string = "<b>{{$category->name}} reported in the area." + "</b><br/>Last report: <span class='timestamp' datetime='" + layer.feature.properties.updated_at + "'>" + layer.feature.properties.updated_at + "</span>.<br/>";
                    // string += "<small><a href='https://explorevillefranche.com/posts/getting-to-villefranche'>Learn how to stay safe on Umbrella</a></small><br/>";
                    @guest
                    //string += "<br/>Log in to confirm or deny this";
                    @else
                    //string += "<br/>";
                    //string += "<button value='Upvote' class='btn btn-primary'><img src='/images/icons/eyes.svg' width='20'></button><button value='Downvote' class='btn'><img src='/images/icons/noeyes.svg' width='20'></button>";
                    @endguest
                    return string;
                });
            }
        });
        markers.addLayer(geojson{{$category->id}});
        @endforeach
    var overlayMaps = {

        @foreach(App\Category::with('incidents')->get() as $category)
            "<img src='{{$category->icon}}' class='rounded img-thumbnail' style='height:35px;' alt='{{ $category->name }}'> {{ $category->name }}": geojson{{$category->id}},
        @endforeach
    };

    var popup = L.popup();
    mymap.addLayer(markers);
//L.control.layers(null, overlayMaps).addTo(mymap);

mymap.on('contextmenu', onMapClick);

//mymap.once('locationfound', onLocationFound);

mymap.once('locationerror', onLocationError);

// var markergeo;
// mymap.once('locationfound', function(ev){
//     if (!markergeo) {
//         markergeo = L.marker(ev.latlng).addTo(mymap);
//     } else {
//         markergeo.setLatLng(ev.latlng);
//     }
// })

mymap.on('baselayerchange', onOverlayAdd);

mymap.on('popupopen', trackPopup);
mymap.on('zoomend', trackZoomChange);
mymap.on('moveend', trackMoveChange);
function content(lng, lat) {
    @guest
    var content = 'Login to report an incident';
    @else
    var content = '<form method="POST" id="reportForm" action="/incidents">@csrf<label class="my-1 mr-2">Report incident:</label><select name="category" class="custom-select my-1 mr-sm-2 custom-select-sm">';
    @foreach(App\Category::get() as $category)
        content += '<option value="{{$category->id}}"><img src="{{$category->icon}}" width="20">{{$category->name}}</option>';
    @endforeach
        content += '</select><br/><input type="hidden" name="lat" value="'+lat+'"><input type="hidden" name="lng" value="'+lng+'">';
        content+= '<input type="submit" value="Report" class="btn btn-primary btn-sm my-1"></form>';

    @endguest
    return content;
}

function onMapClick(e) {
    popup
        .setLatLng(e.latlng)
        // "Clicked at " + e.latlng.toString()+ "<br/><br/>" +
        .setContent(content(e.latlng.lng, e.latlng.lat))
        .openOn(mymap);
        mymap.flyTo(e.latlng, 19);

}
$('#reportForm').on('submit', function(e) {
    goForm(e);
});
function goForm(e) {
        console.log(e);
        alert(e);
       e.preventDefault();
       var name = $('#name').val();
       var message = $('#message').val();
       var postid = $('#post_id').val();
       $.ajax({
           type: "POST",
           url: '/comment/add',
           data: {name:name, message:message, post_id:postid},
           success: function( msg ) {
               alert( msg );
                submitForm(e.latlng.lng, e.latlng.lat, 1);
           }
       });
   };

function submitForm(lng, lat, category) {
    window['geojson'+category].addData({ "type": "Feature", "geometry": { "type": "Point", "coordinates": [lng, lat] } });
}

// function onLocationFound(e) {
//     var radius = e.accuracy / 2;

//     L.marker(e.latlng).addTo(mymap)
//         .bindPopup("You are within " + radius + " meters from this point").openPopup();

//     L.circle(e.latlng, radius).addTo(mymap);

//     dataLayer.push({'event': 'User location found'});
// }

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
      // Construct an empty list to fill with onscreen markers.
    var inBounds = [],
    // Get the map bounds - the top-left and bottom-right locations.
    bounds = mymap.getBounds();

    // For each marker, consider whether it is currently visible by comparing
    // with the current map bounds.
    markers.eachLayer(function(marker) {
        if (bounds.contains(marker.getLatLng())) {
            inBounds.push(marker.feature.properties.category);
        }
    });

    // Display a list of markers.
    document.getElementById('coordinates').innerHTML = inBounds.join(', ');
}

function trackPopup(e){
    timeago.render(document.querySelectorAll('.timestamp'));
    if(e.popup._source && e.popup._source.feature) {
        dataLayer.push({'event': 'Map popup open', 'id': "Feature "+e.popup._source.feature.properties.full_id});
    } else if (e.popup._source) {
    dataLayer.push({'event': 'Map popup open', 'id': "QR Code "+e.popup._latlng.toString()});
    } else if (e.popup._latlng) {
        dataLayer.push({'event': 'Map popup open', 'id': "Position "+e.popup._latlng.toString()});
    }
}



//Leaflet URL Hash
    (function(window) {
    var HAS_HASHCHANGE = (function() {
        var doc_mode = window.documentMode;
        return ('onhashchange' in window) &&
            (doc_mode === undefined || doc_mode > 7);
    })();

    L.Hash = function(map) {
        this.onHashChange = L.Util.bind(this.onHashChange, this);

        if (map) {
            this.init(map);
        }
    };

    L.Hash.parseHash = function(hash) {
        if(hash.indexOf('#') === 0) {
            hash = hash.substr(1);
        }
        var args = hash.split("/");
        if (args.length == 3) {
            var zoom = parseInt(args[0], 10),
            lat = parseFloat(args[1]),
            lon = parseFloat(args[2]);
            if (isNaN(zoom) || isNaN(lat) || isNaN(lon)) {
                return false;
            } else {
                return {
                    center: new L.LatLng(lat, lon),
                    zoom: zoom
                };
            }
        } else {
            return false;
        }
    };

    L.Hash.formatHash = function(map) {
        var center = map.getCenter(),
            zoom = map.getZoom(),
            precision = Math.max(0, Math.ceil(Math.log(zoom) / Math.LN2));

        return "#" + [zoom,
            center.lat.toFixed(precision),
            center.lng.toFixed(precision)
        ].join("/");
    },

    L.Hash.prototype = {
        map: null,
        lastHash: null,

        parseHash: L.Hash.parseHash,
        formatHash: L.Hash.formatHash,

        init: function(map) {
            this.map = map;

            // reset the hash
            this.lastHash = null;
            this.onHashChange();

            if (!this.isListening) {
                this.startListening();
            }
        },

        removeFrom: function(map) {
            if (this.changeTimeout) {
                clearTimeout(this.changeTimeout);
            }

            if (this.isListening) {
                this.stopListening();
            }

            this.map = null;
        },

        onMapMove: function() {
            // bail if we're moving the map (updating from a hash),
            // or if the map is not yet loaded

            if (this.movingMap || !this.map._loaded) {
                return false;
            }

            var hash = this.formatHash(this.map);
            if (this.lastHash != hash) {
                location.replace(hash);
                this.lastHash = hash;
            }
        },

        movingMap: false,
        update: function() {
            var hash = location.hash;
            if (hash === this.lastHash) {
                return;
            }
            var parsed = this.parseHash(hash);
            if (parsed) {
                this.movingMap = true;

                this.map.setView(parsed.center, parsed.zoom);

                this.movingMap = false;
            } else {
                this.onMapMove(this.map);
            }
        },

        // defer hash change updates every 100ms
        changeDefer: 100,
        changeTimeout: null,
        onHashChange: function() {
            // throttle calls to update() so that they only happen every
            // `changeDefer` ms
            if (!this.changeTimeout) {
                var that = this;
                this.changeTimeout = setTimeout(function() {
                    that.update();
                    that.changeTimeout = null;
                }, this.changeDefer);
            }
        },

        isListening: false,
        hashChangeInterval: null,
        startListening: function() {
            this.map.on("moveend", this.onMapMove, this);

            if (HAS_HASHCHANGE) {
                L.DomEvent.addListener(window, "hashchange", this.onHashChange);
            } else {
                clearInterval(this.hashChangeInterval);
                this.hashChangeInterval = setInterval(this.onHashChange, 50);
            }
            this.isListening = true;
        },

        stopListening: function() {
            this.map.off("moveend", this.onMapMove, this);

            if (HAS_HASHCHANGE) {
                L.DomEvent.removeListener(window, "hashchange", this.onHashChange);
            } else {
                clearInterval(this.hashChangeInterval);
            }
            this.isListening = false;
        }
    };
    L.hash = function(map) {
        return new L.Hash(map);
    };
    L.Map.prototype.addHash = function() {
        this._hash = L.hash(this);
    };
    L.Map.prototype.removeHash = function() {
        this._hash.removeFrom();
    };
})(window);

(function() {
    var RestoreViewMixin = {
        restoreView: function () {
            if (!storageAvailable('localStorage')) {
                return false;
            }
            var storage = window.localStorage;
            if (!this.__initRestore) {
                this.on('moveend', function (e) {
                    if (!this._loaded)
                        return;  // Never access map bounds if view is not set.

                    var view = {
                        lat: this.getCenter().lat,
                        lng: this.getCenter().lng,
                        zoom: this.getZoom()
                    };
                    storage['mapView'] = JSON.stringify(view);
                }, this);
                this.__initRestore = true;
            }

            var view = storage['mapView'];
            try {
                view = JSON.parse(view || '');
                this.setView(L.latLng(view.lat, view.lng), view.zoom, true);
                return true;
            }
            catch (err) {
                return false;
            }
        }
    };

    function storageAvailable(type) {
        try {
            var storage = window[type],
                x = '__storage_test__';
            storage.setItem(x, x);
            storage.removeItem(x);
            return true;
        }
        catch(e) {
            console.warn("Your browser blocks access to " + type);
            return false;
        }
    }

    L.Map.include(RestoreViewMixin);
})();

    if (!mymap.restoreView()) {
        mymap.setView([50.5, 30.51], 3);
    }
    var hash = new L.Hash(mymap);

</script>

@endsection
