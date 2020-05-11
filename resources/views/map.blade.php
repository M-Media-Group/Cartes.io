@extends('layouts.clean', ['index_and_googlebots' => $map->privacy == 'public' ? true : false])

@section('title', $map->title ?? "Untitled map")
@section('meta_description', $map->description)
@section('meta_image', config('app.url').'/images/map.png')

@section('above_container')
    <map-component map_id="{{$map->uuid}}" map_token="{{$token}}" style="height: 76vh;" users_can_create_incidents="{{$map->users_can_create_incidents}}" :map_categories="{{$map->categories}}"></map-component>
@endsection
@section('content')
<h1 style="display: none;">{{config('app.name')}}</h1>
	<map-details-component map_id="{{$map->uuid}}" map_token="{{$token}}" v-bind:map="{{$map}}"></map-details-component>

	@if($token)
		<div class="modal fade" style="background: linear-gradient(rgba(28,119,195, 0.2), var(--white));" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		  <div class="modal-dialog modal-dialog-centered modal-dark" role="document">
		    <div class="modal-content bg-dark">
		      <div class="modal-header">
		        <h5 class="modal-title" id="exampleModalLabel">Here's your new map</h5>

		      </div>
		      <div class="modal-body">
		        On your map, right click (long-tap on mobile) to create your first marker.
		      </div>
		      <div class="modal-footer">
		        <button type="button" class="btn btn-primary" data-dismiss="modal">Lets go!</button>
		      </div>
		    </div>
		  </div>
		</div>
	@endif
@endsection
@if($token)
	@section('footer_scripts')
		<script type="text/javascript">
			dataLayer.push({ event: 'map-create' });
			$('#exampleModal').modal('show')
		</script>
	@endsection
@endif
<script type="application/ld+json">
{
  "@context": "http://schema.org",
  "@type": "Place",
  "geo": {
    "@type": "GeoCoordinates",
    "latitude": "40.75",
    "longitude": "73.98"
  },
  "name": "Empire State Building"
}
</script>
