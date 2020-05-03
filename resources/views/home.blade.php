@extends('layouts.app')

@section('above_container')
<div class="jumbotron jumbotron-fluid d-flex align-items-center" style="min-height: 69vh; background: url('/images/earth.jpg')">
  <div class="container">
      <h1 class="display-4">Live maps for everyone</h1>
      <p class="lead">Create free anonymous or public maps without even having to sign up.</p>
{{--       <p>Your maps are stored in your browser.</p>
 --}}
 <div>
 <form method="POST" action="/maps" style="display: inline;">
    @csrf
    <button type="submit" class="btn btn-primary btn-lg mt-3">
    {{ __('Create a new map') }}
    </button>
    </form>
    <a class="btn btn-secondary btn-lg mt-3" href="/register">Sign up, if you want</a>
  </div>
  </div>
</div>
@endsection

@section('left_sidebar')
{{-- test
 --}}@endsection

@section('content')
<my-maps-component></my-maps-component>

@endsection
