<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('components.head', [ 'index_and_googlebots' => isset($index_and_googlebots) ? $index_and_googlebots : true ])
</head>

<body>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ config('blog.google_tag_id') }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <div id="app">
        @include('components.nav')
        @yield('above_container')
        <div class="container">
            <div class="row justify-content-center mt-5">
                <main class="col-md-6 order-md-6">
                    @yield('content')
                </main>
                <div class="col-md-3 order-md-12 custom-small-text">
                    <hr>
                    @section('sidebar')
                    @if( config('blog.instagram_username'))
                    {{ config('app.name') }} on <a href="{{config('blog.instagram_url')}}">Instagram</a>!
                    <hr>
                    @endif
                    <p>{{ config('app.name') }}.</p>
                    <p>This is an open source project. Feel free to contribute to the development on <a href="https://github.com/mwargan/IncidentReport">GitHub</a></p>
                    @if(!Auth::check())
                    <p><a href="/register">Sign up</a> to {{ config('app.name', 'us') }} to get more info, make maps private, and get updates as the project grows.</p>
                    @endif
                    <hr>
                    <small class="mb-3">
                        <a href="/about" class="text-muted">About</a>
                        <a href="/privacy-policy" class="text-muted">Privacy policy</a>
                        <a class="text-muted" href="/terms-and-conditions">Terms and conditions</a>
                    </small>
                    @show
                </div>
                <div class="col-md-3 order-md-1">
                    @yield('left_sidebar')
                </div>
            </div>
        </div>
        <div class="footer">
        </div>
    </div>
    @include('components.footer')
