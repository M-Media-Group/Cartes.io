@extends('layouts.clean')

@section('title', 'Live maps for everyone and everything')
@section('meta_description', 'Create free anonymous or public maps without even having to sign up.')
@section('meta_image', config('app.url').'/images/map.png')

@section('above_container')
<div class="jumbotron jumbotron-fluid d-flex align-items-center" style="min-height: 33rem;height: 71vh; max-height:600px; background: linear-gradient(rgba(28,119,195, 0.2), var(--white)), url('/images/earth.jpg') no-repeat; background-size: cover;color:#fff;">
  <div class="container mt-4 mb-3">
      <h1>Create maps to share with friends</h1>
      <p class="lead">Whether its marking places to visit for out-of-town friends or marking possible spots for your next party</p>
{{--       <p>Your maps are stored in your browser.</p>
 --}}
    <div>
      <form method="POST" action="/maps" style="display: inline;">
        @csrf
        <button type="submit" class="btn btn-primary btn-lg mt-3">
        {{ __('Create a new map for friends') }}
        </button>
      </form>
      <a class="btn btn-dark btn-lg mt-3" href="/register">Sign up, if you want</a>
    </div>
  </div>
</div>

@endsection

@section('content')
<div class="row align-items-center mb-5">
  <div class="col-md-6 text-center">
    <form method="POST" action="/maps" class="d-flex align-items-center" style="height:250px;">
      @csrf
      <button type="submit" class="btn btn-primary btn-lg mt-3" style="margin:0 auto;">
      {{ __('New map') }}
      </button>
    </form>
  </div>
   <div class="col-md-6">
      <h2>1. Create a map</h2>
      <p>Tap on the blue "New map" button in the top right corner of every page.</p>
    </div>
</div>
<div class="row align-items-center mb-5 mt-5">
  <div class="col-md-6 text-center">
    <img src="/images/marker-01.svg" class="img-fluid mb-3" style="height:250px;object-fit: cover;" alt="">
  </div>
   <div class="col-md-6">
      <h2>2. Place markers on your map</h2>
      <p>Choose from one of the existing markers or add your own.</p>
    </div>
</div>
<div class="row mb-5 mt-5 align-items-center">
  <div class="col-md-6">
    <img src="/images/map.png" class="img-fluid mb-3" style="height:250px;object-fit: cover;" alt="">
  </div>
   <div class="col-md-6">
      <h2>3. Share the link</h2>
      <p>Press the share button to share the map with your friends. By default, only people with a link can see your map.</p>
    </div>
</div>
@endsection
