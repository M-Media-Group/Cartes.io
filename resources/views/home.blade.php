@extends('layouts.app')

@section('left_sidebar')
@if(Auth::user()->can('apply to write') && Auth::user()->hasVerifiedEmail())
                <div class="card text-center mb-3 mt-3">
                  <div class="card-body">
                    <h5 class="card-title">Write for {{config('app.name')}}</h5>
                    <p class="card-text">Want to contribute articles to {{config('app.name')}}?</p>
                    <a href="/write" class="btn btn-primary">Start writing</a>
                  </div>
                </div>
            @endif
 @foreach($categories as $category)
    <hr>
    <a href="/categories/{{$category->slug}}">
        <img class="rounded img-thumbnail mr-1" height="25" width="25" src="{{$category->icon}}" alt="{{$category->name}}">{{ $category->name }}
    </a>
 @endforeach
@endsection

@section('content')

            <div class="card mb-3 mt-3">
                <div class="card-header">Hey {{Auth::user()->name}}!</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    Here your most recently viewed posts will show.
                    @if(Auth::user()->hasVerifiedEmail() == false)
                        <strong>Please verify your email address.</strong>
                    @endif
                </div>
            </div>
        @foreach($posts as $post)
        <a href="/posts/{{$post->slug}}" title="{{ $post->title }}">
            <img src="{{$post->header_image}}" class="rounded img-thumbnail mb-2" alt="{{ $post->title }}" >
            <h2>{{ $post->title }}</h2>
            <p class="text-muted">{{ $post->excerpt }}</p>
            <p class="text-muted">
                @foreach($post->categories as $category)
                    <a href="/categories/{{$category->slug}}"><img class="rounded img-thumbnail mr-1" height="30" width="30" src="{{$category->icon}}" alt="{{$category->name}}">{{$category->name}}</a>
                @endforeach
            </p>
            <hr>
        </a>
    @endforeach

@endsection
