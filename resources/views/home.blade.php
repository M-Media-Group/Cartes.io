@extends('layouts.app')

@section('title', 'Live maps for everyone and everything')
@section('meta_description', 'Create free anonymous or public maps without even having to sign up.')

@section('above_container')
<div class="jumbotron jumbotron-fluid d-flex align-items-center" style="min-height: 77vh; background: url('/images/earth.jpg')">
  <div class="container mt-4 mb-3">
      <h1 class="display-5">Live maps for everyone and everything</h1>
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
    <a class="btn btn-dark btn-lg mt-3" href="/register">Sign up, if you want</a>
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
