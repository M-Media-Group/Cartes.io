@extends('layouts.clean')

@section('content')
<h1>Before you start reporting incidents, we need you to read our rules</h1>
<br/>
<div class="card bg-dark text-white">
  <img class="card-img-top" src="/images/correct-mapping-1.jpg" alt="Card image">
  <div class="card-body">
    <h5 class="card-title"><b>Be as accurate as possible</b> when reporting locations of incidents</h5>
    <p class="card-text">Report exactly where the incidents are. If there's intersections blocked off, mark the ends of each street to where the incident extends to. If an incident extends across multiple streets, report where the incident is on each street.</p>
    <p class="card-text">Avoid reporting incidents that are already marked on the map. Instead, confirm or deny these existing reports.</p>
  </div>
</div>
<div class="card bg-dark text-white mt-5">
  <img class="card-img-top" src="/images/correct-mapping-2.jpg" alt="Card image">
  <div class="card-body">
    <h5 class="card-title">Be respectful of the law and <b>don't report locations of incidents if it breaks local laws, reveals classified or sensitive information, and/or causes more harm than good</b></h5>
    <p class="card-text">Use common sense and be aware of how your actions affect others.</p>
  </div>
</div>
<br/>
@markdown
@endmarkdown
<br/>
{{--  && Auth::user()->hasVerifiedEmail() RE-ADD this when ready with emails --}}
    @if(Auth::user()->can('apply to report'))
    	<a href="#" class="btn btn-primary mb-3" onclick="event.preventDefault();
                                             document.getElementById('reporter-form').submit();">I understand and accept these rules
        </a>
        <form id="reporter-form" action="/me/apply/reporter" method="POST" style="display: none;">
            @csrf
        </form>
   	@elseif(!Auth::user()->hasVerifiedEmail())
    	<p class="text-danger font-weight-bold">You must verify your email to publish posts.</p>
    @endif
    <a href="{{ url()->previous() }}" class="btn btn-secondary mb-3">Go back</a>
@endsection
