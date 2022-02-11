@extends('layouts.app')

@section('title', 'Live maps for everyone and everything')
@section('meta_description', 'Create free anonymous or public maps without even having to sign up.')
@section('meta_image', config('app.url') . '/images/map.png')

@section('above_container')
    <div class="jumbotron jumbotron-fluid d-flex align-items-center"
        style="min-height: 33rem;height: 71vh; max-height:600px; background: linear-gradient(rgba(28,119,195, 0.2), var(--white)), url('/images/earth.jpg') no-repeat; background-size: cover;color:#fff;">
        <div class="container mt-4 mb-3">
            <h1>Maps for everyone and everything</h1>
            <p class="lead">Create maps, add markers, and share anywhere without even having to sign up.</p>
            {{-- <p>Your maps are stored in your browser.</p> --}}
            <div>
                <form method="POST" action="/maps" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-lg mt-3">
                        {{ __('Create a new map') }}
                    </button>
                </form>
                @guest
                    <a class="btn btn-dark btn-lg mt-3" href="/register">Sign up, if you want</a>
                @endguest
            </div>
        </div>
    </div>
@endsection

@section('left_sidebar')
    {{-- <h3>Create a map from a template</h3>
<div class="card bg-primary text-white text-center mb-3">
    <blockquote class="blockquote mb-0">
      <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer posuere erat.</p>
      <footer class="blockquote-footer">
        <small>
          Someone famous in <cite title="Source Title">Source Title</cite>
        </small>
      </footer>
    </blockquote>
  </div> --}}
@endsection

@section('content')
    <my-maps-component></my-maps-component>

@endsection
