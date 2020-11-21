@extends('layouts.clean')

@section('content')
<h1>Before you start creating maps, we need you to read our rules</h1>
<br/>
@if(!Auth::user()->hasVerifiedEmail())
<div class="card text-dark">
{{--   <img class="card-img-top" src="/images/correct-mapping-1.jpg" alt="Card image">
 --}}  <div class="card-body">
    <h5 class="card-title"><b>Confirm your email address</b></h5>
    <p class="card-text">You won't be able to do much until you confirm your email address. Click the link we sent you.</p>
  </div>
</div>
@endif
<div class="card bg-dark text-white mt-5">
  <img class="card-img-top" src="/images/correct-mapping-1.jpg" alt="Card image">
  <div class="card-body">
    <h5 class="card-title"><b>Be as accurate as possible</b> when creating markers</h5>
    <p class="card-text">Report exactly where the marker should be. Zoom in all the way for the best accuracy.</p>
    <p class="card-text">Avoid creating duplicate markers. Instead, confirm or deny these existing markers.</p>
  </div>
</div>
<div class="card bg-dark text-white mt-5">
  <img class="card-img-top" src="/images/correct-mapping-2.jpg" alt="Card image">
  <div class="card-body">
    <h5 class="card-title">Be respectful of the law and <b>don't report locations if it breaks local laws, reveals classified or sensitive information, and/or causes more harm than good</b></h5>
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
