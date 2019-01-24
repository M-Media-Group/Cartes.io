<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title') | {{ config('app.name', 'Laravel') }}</title>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="keywords" content="{{ config('app.name') }},@yield('meta_keywords')">
    <meta name="description" content="@yield('meta_description', config('app.name') .' was made by English speaking residents of this beautiful city on the French Riviera. This site aims to show off the beauty of this city and make it more accessible to people that decide to visit Villefranche sur Mer.')">
    <meta name="language" content="{{ str_replace('_', '-', app()->getLocale()) }}">
    <meta name="robots" content="index,follow">
    <meta name="googlebot" content="index,follow">
    <meta name="google" content="nositelinkssearchbox">
    <meta name="google-site-verification" content="verification_token">

    <meta name="author" content="@yield('meta_author', config('app.name'))">
    <meta name="coverage" content="Worldwide">
    <meta name="distribution" content="Global">

    <meta property="fb:app_id" content="291562968144309">
    <meta property="og:url" content="{{url()->full()}}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title', config('app.name'))">
    <meta property="og:image" content="@yield('meta_description', config('app.url').'/images/logo.svg')">
    <meta property="og:description" content="@yield('meta_description', config('app.name') .' was made by English speaking residents of this beautiful city on the French Riviera. This site aims to show off the beauty of this city and make it more accessible to people that decide to visit Villefranche sur Mer.')">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">
    <meta property="article:author" content="@yield('meta_author', config('app.name'))">

    <!-- STAY - Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    @yield('header_scripts')

    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-KBHVSGP');</script>
    <!-- End Google Tag Manager -->
</head>
<body>
     <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-KBHVSGP"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light navbar-laravel">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                        <img src="/images/logo.svg" width="45" height="45" alt="{{config('app.name')}}">
                        {{ config('app.name', 'Explore Villefranche') }}
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Authentication Links -->
                        @guest
                            <li class="nav-item">
                                <a class="nav-link" href="/posts">{{ __('Posts') }}</a>
                            </li>
                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="/categories">{{ __('Categories') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    @if(Auth::user()->hasVerifiedEmail())
                                        <a class="dropdown-item" href="/posts/create">
                                            {{ __('Create post') }}
                                        </a>
                                        <a class="dropdown-item" href="/categories/create">
                                            {{ __('Create category') }}
                                        </a>
                                    @endif
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>
        @yield('above_container')
        <div class="container">
            <div class="row justify-content-center">

                <main class="col-md-6 order-md-6">
                    @yield('content')
                </main>
                <div class="col-md-3 order-md-12 custom-small-text">
                    <hr>
                    @section('sidebar')
                        {{ config('app.name', 'Laravel') }} on <a href="https://instagram.com/villefranchesurmer">Instagram</a>!
                        <hr>
                        <p>{{ config('app.name', 'Laravel') }} was made by English speaking residents of this beautiful city on the French Riviera. This site aims to show off the beauty of this city and make it more accessible to people visiting.</p>
                        <hr>
                        <small class="mb-3"><a href="/privacy-policy" class="text-muted">Privacy policy</a> <a class="text-muted" href="/terms-and-conditions">Terms and conditions</a></small>
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
