@extends('layouts.clean', ['index_and_googlebots' => $map->privacy == 'public' ? true : false])

@section('title', $map->title ?? "Untitled map")
@section('meta_description', $map->description)
@section('meta_image', config('app.url').'/images/map.jpg')

@section('above_container')
    <map-component map_id="{{$map->uuid}}" map_token="{{$token}}" style="height: 76vh;" users_can_create_incidents="{{$map->users_can_create_incidents}}" :map_categories="{{$map->categories}}"></map-component>
@endsection
@section('content')
<h1 style="display: none;">{{config('app.name')}}</h1>
	<map-details-component map_id="{{$map->uuid}}" map_token="{{$token}}" v-bind:map="{{$map}}"></map-details-component>
@endsection
@if($token)
	@section('footer_scripts')
		<script type="text/javascript">
			dataLayer.push({ event: 'map-create' });
		</script>
	@endsection
@endif
