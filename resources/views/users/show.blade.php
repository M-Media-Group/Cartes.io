@extends('layouts.app')

@section('title', $user->username )

@section('content')
			<div class="mt-3">
			    <img src="{{$user->avatar}}" class="rounded img-thumbnail float-left mr-3 w-25" >
			    <h1>{{ $user->username }}</h1>
			    <span>Member</span>
			    @can('update', $user)
			        <a href="/users/{{$user->username}}/edit">
			            {{ __('Edit') }}
			        </a>
			    @endcan
			</div>
		    <hr class="w-100 float-left">
		    <h2 class="float-left">Posts</h2>
	@foreach($user->posts as $post)
		<a href="/posts/{{$post->slug}}">
		    <img src="{{$post->header_image}}" class="rounded img-thumbnail mb-2" >
		    <h1>{{ $post->title }}</h1>
		    <p class="text-muted">{{ $post->excerpt }}</p>
		    <hr>
		</a>
	@endforeach
@endsection
