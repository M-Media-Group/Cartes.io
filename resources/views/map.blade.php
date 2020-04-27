@extends('layouts.clean')

@section('title', 'Incident Report')
@section('meta_description', "Interactive map of incidents that may be dangerous to activists, human rights defenders, aid workers, social workers, NGO staff, or journalists.")
@section('meta_image', config('app.url').'/images/map.jpg')



@section('above_container')
    <map-component></map-component>
@endsection
@section('content')
<h1 style="display: none;">{{config('app.name')}}</h1>
<div class="row">
<div class="col-sm-8">
<p>Right click (or long-tap on mobile) on the map to report incidents that may be dangerous to activists, journalists, human rights defenders, aid workers, social workers, or NGO staff during times of unrest or protest.</p>
<p>After 59 minutes, your report will automatically dissapear from the map.</p>
<p class="text-muted small">{{App\Incident::withoutGlobalScopes()->count()}} incidents have been reported so far.</p>
<p class="text-muted small">You're currently looking at: <span id='coordinates'>No incidents</span>.</p>
</div>
<div class="col-sm-4">
    <h4 class="small text-muted">Protests mentioned in the news</h4>
    @foreach ($items as $item)
        @if (strpos($item->get_description(), 'rotest') !== false || strpos($item->get_title(), 'rotest') !== false)
            <rss title="{!! $item->get_title() !!}" date="{{ Carbon\Carbon::parse($item->get_date())->diffForHumans() }}" source="{{ $item->get_feed()->get_title() }}" link="{{ $item->get_permalink() }}"></rss>

        @endif
    @endforeach
</div>
</div>
{{-- <button class="btn btn-primary mb-3" onclick="mymap.locate({setView: true, maxZoom: 18, watch: false});">Find my location on the map</button> --}}

@endsection
