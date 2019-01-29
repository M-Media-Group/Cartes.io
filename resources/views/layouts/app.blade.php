<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('components.head')
</head>
<body>
     <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ config('blog.google_tag_id') }}"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <div id="app">
        @include('components.nav')
        @yield('above_container')
        <div class="container">
            <div class="row justify-content-center">

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
                        <p>{{ config('app.name') }} was made by English speaking residents of this area on the French Riviera. This site aims to show off the beauty of this region and make it more accessible to people visiting.</p>
                        @if(!Auth::check())
                        <p><a href="/register">Sign up</a> to {{ config('app.name', 'us') }} to get more info and updates.</p>
                        @endif
                        <hr>
                        <small class="mb-3">
                            <a href="/privacy-policy" class="text-muted">Privacy policy</a>
                            <a class="text-muted" href="/terms-and-conditions">Terms and conditions</a>
                            @if(!Auth::check())
                                <a class="text-muted" href="/login">Login</a>
                            @endif
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
</body>
</html>
