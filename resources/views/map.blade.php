@extends('layouts.clean')

@section('title', 'Incident Report')
@section('meta_description', "Interactive map of incidents that may be dangerous to activists, human rights defenders, aid workers, social workers, NGO staff, or journalists.")
@section('meta_image', config('app.url').'/images/map.jpg')



@section('above_container')
    <map-component map_id="{{$map->uuid}}" map_token="{{$token}}"></map-component>
@endsection
@section('content')
<h1 style="display: none;">{{config('app.name')}}</h1>
<div class="row">
<div class="col-sm-7">
	    <map-details-component map_id="{{$map->uuid}}" map_token="{{$token}}" v-bind:map="{{$map}}"></map-details-component>


	<div class="small">
		<h3>About the tracker</h3>
		<p>{{App\Incident::withoutGlobalScopes()->count()}} incidents have been reported so far.</p>
		<p>{{config('app.name')}} is an <a href="https://github.com/mwargan/IncidentReport" rel="noopener noreferer" target="_BLANK">open-source</a> project to help activists, journalists, human rights defenders, aid workers, social workers, or NGO staff during times of unrest or protest.
		</p>
	</div>
</div>
<div class="col-sm-5 p-sm-0">
	@if($map->users_can_create_incidents == 'yes' || $token)
	<div class="small text-muted mb-3">Right click (or long-tap on mobile) on the map to create a marker. You can choose one of the existing labels or create your own.</div>
	@endif
    @if($token)
          <div class="card bg-dark text-white">
                <div class="card-header">{{ __('Map settings') }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="form-group row">
                            <label for="title" class="col-md-12 col-form-label">{{ __('Slug') }}</label>

                            <div class="col-md-12">
                                <input id="title" type="text" class="form-control{{ $errors->has('title') ? ' is-invalid' : '' }}" name="title" value="{{ $map->slug ?? old('title') }}" required>

                                @if ($errors->has('title'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('title') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                        	<label for="password-confirm" class="col-md-12 col-form-label">{{ __('Who can see this map') }}</label>
		                    <div class="col-md-12">
		                        <div class="form-check">
								  <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios1" value="option1" checked>
								  <label class="form-check-label" for="exampleRadios1">
								    Everyone
								  </label>
								</div>
								<div class="form-check">
								  <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios2" value="option2">
								  <label class="form-check-label" for="exampleRadios2">
								    Only people with a link
								  </label>
								</div>
								<div class="form-check disabled">
								  <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios3" value="option3" disabled>
								  <label class="form-check-label" for="exampleRadios3">
								    No one
								  </label>
								</div>
		                    </div>
		                </div>

		                <div class="form-group row">
                        	<label for="password-confirm" class="col-md-12 col-form-label">{{ __('Who can create incidents') }}</label>
		                    <div class="col-md-12">
		                        <div class="form-check">
								  <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios1" value="option1" checked>
								  <label class="form-check-label" for="exampleRadios1">
								    Everyone
								  </label>
								</div>
								<div class="form-check">
								  <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios2" value="option2">
								  <label class="form-check-label" for="exampleRadios2">
								    Only logged in people
								  </label>
								</div>
								<div class="form-check disabled">
								  <input class="form-check-input" type="radio" name="exampleRadios" id="exampleRadios3" value="option3" disabled>
								  <label class="form-check-label" for="exampleRadios3">
								    No one
								  </label>
								</div>
		                    </div>
		                </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Reset Password') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
    @else
    @endif
{{--     @foreach ($items as $item)
        @if (strpos($item->get_description(), 'rotest') !== false || strpos($item->get_title(), 'rotest') !== false)
            <rss title="{!! $item->get_title() !!}" date="{{ Carbon\Carbon::parse($item->get_date())->diffForHumans() }}" source="{{ $item->get_feed()->get_title() }}" link="{{ $item->get_permalink() }}"></rss>

        @endif
    @endforeach --}}
</div>
</div>
{{-- <button class="btn btn-primary mb-3" onclick="mymap.locate({setView: true, maxZoom: 18, watch: false});">Find my location on the map</button> --}}

@endsection
@section('footer_scripts')

@endsection
