@extends('layouts.clean', ['index_and_googlebots' => $map->privacy == 'public' ? true : false])

@section('title', $map->title ?? "Untitled map")
@section('meta_description', $map->description)
@section('meta_image', config('app.url').'/images/map.png')

@section('above_container')
    {{-- <map-component map_id="{{$map->uuid}}" map_token="{{$token}}" style="height: 76vh;" users_can_create_markers="{{$map->users_can_create_markers}}" :map_categories="{{$map->categories}}"></map-component> --}}

    <map-container-component :initial_map="{{$map}}" initial_map_token="{{$token}}" :user="{{Auth::user() ?? "{}" }}"></map-container-component>

@endsection
@section('content')
<h1 style="display: none;">{{config('app.name')}}</h1>
{{-- 	<map-details-component map_id="{{$map->uuid}}" map_token="{{$token}}" v-bind:map="{{$map}}"></map-details-component> --}}

	@if($token)
		<div class="modal fade" style="background: linear-gradient(rgba(28,119,195, 0.2), var(--white));" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		  <div class="modal-dialog modal-dialog-centered modal-dark" role="document">
		    <div class="modal-content bg-dark">
		      <div class="modal-header">
		        <h5 class="modal-title" id="exampleModalLabel">Yipee! You've made a new map</h5>

		      </div>
		      <div class="modal-body">
		        <p>On your map, right click (long-tap on mobile) to create your first marker.</p>
		        <p>Scroll down a bit and click on "Map settings" to edit privacy and marker expiration times. Don't forget to edit the title and description too!</p>
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
