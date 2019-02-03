@extends('layouts.app')

@section('title', $post->title)
@section('meta_description', $post->excerpt)
@section('meta_author', config('app.url').'/users/'.$post->user->username)
@section('meta_fb_type', 'article')
@section('meta_image', $post->header_image)


@section('header_scripts')
    <meta property="og:updated_time" content="{{$post->updated_at}}" />
    <meta property="article:published_time" content="{{$post->updated_at}}" />
    <meta property="article:modified_time" content="{{$post->updated_at}}" />
    <meta property="article:section" content="@if (isset($post->categories[0])) {{$post->categories[0]->name}} @endif" />

    <script type="application/ld+json">
        {
            "@context":"http://schema.org",
            "@type": "BlogPosting",
            "image": "{{$post->header_image}}",
            "url": "{{url()->full()}}",
            "headline": "{{$post->title}}",
            "dateCreated": "{{$post->created_at}}",
            "datePublished": "{{$post->published_at}}",
            "dateModified": "{{$post->updated_at}}",
            "inLanguage": "en-FR",
            "isFamilyFriendly": "true",
            "copyrightYear": "{{ now()->year }}",
            "copyrightHolder": "",
            "contentLocation": {
                "@type": "Place",
                "name": "Villefranche sur Mer, France"
            },
            "accountablePerson": {
                "@type": "Person",
                "name": "{{$post->user->username}}",
                "url": "{{ config('app.url') }}/users/{{$post->user->username}}"
            },
            "author": {
                "@type": "Person",
                "name": "{{$post->user->username}}",
                "url": "{{ config('app.url') }}/users/{{$post->user->username}}"
            },
            "creator": {
                "@type": "Person",
                "name": "{{$post->user->username}}",
                "url": "{{ config('app.url') }}/users/{{$post->user->username}}"
            },
            "publisher": {
                "@type": "Organization",
                "name": "{{ config('app.name') }}",
                "url": "{{ config('app.url') }}",
                "logo": {
                    "@type": "ImageObject",
                    "url": "{{config('blog.logo_url') }}",
                    "width":"60",
                    "height":"60"
                }
            },
            "mainEntityOfPage": "True",
            "keywords": [
            @foreach($post->categories as $category)
                "{{$category->name}}",
            @endforeach
                "{{ config('app.name') }}"
            ],
            "genre":["Travel",
            @foreach($post->categories as $category)
                "{{$category->name}}",
                "Explore"
            @endforeach
            ],
            "articleSection": "Uncategorized posts",
            "articleBody": "{{ $post->body_markdown }}"
        }
    </script>
@endsection

@section('left_sidebar')

@endsection

@section('sidebar')
    @can('update', $post)
        <p>
            <a href="/posts/{{$post->slug}}/edit">
                {{ __('Edit post') }}
            </a>
        </p>
        <hr>
    @endcan
    @can('delete', $post)
        <p>
            <form id="delete-form" action="/posts/{{$post->id}}" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
            <a href="/posts/{{$post->id}}"
               onclick="event.preventDefault();if (confirm('Are you sure?')) { document.getElementById('delete-form').submit() };">
                {{ __('Delete post') }}
            </a>
        </p>
        <hr>
    @endcan
    <p><a href="/users/{{$post->user->username}}"><img class="rounded img-thumbnail mr-1" src="{{$post->user->avatar}}" height="45" width="45" alt="{{$post->user->username}}" title="{{$post->user->username}}">{{$post->user->username}}</a></p>
    <p class="mb-0">Last updated {{ $post->updated_at->diffForHumans() }}</p>
    @if ($post->published_at)
        <small>Published {{ $post->published_at->diffForHumans() }}</small>
    @endif

    <hr>
    @foreach($post->categories as $category)
        <a href="/categories/{{$category->slug}}"><img class="rounded img-thumbnail mr-1" height="30" width="30" src="{{$category->icon}}"  title="{{$category->name}}" alt="{{$category->name}}">{{$category->name}}</a>
    @endforeach
    <hr>
    @parent
@endsection

@section('above_container')

        <img src="{{$post->header_image}}" title="{{$post->title}}" style="height: 63vh;object-fit:cover;width:100%;" alt="{{ $post->title }}">

@endsection

@section('content')
    <article>
        <h1 class="mb-4">{{ $post->title }}</h1>
        @if ($post->published_at)
            <div class="text-justify">
                @markdown{{ $post->body_markdown }}
                @endmarkdown
            </div>
        @else
            <p>Stay tuned! This post is not yet published.</p>
        @endif
    </article>
@endsection
