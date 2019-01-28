@extends('layouts.app')

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
                    @elseif(Auth::user()->canNot('create posts'))
                    If you would like to write for {{config('app.name')}}, please <a href="#">click here</a>.
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
