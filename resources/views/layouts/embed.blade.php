<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport"
        content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0, viewport-fit=cover">

    <title>@yield('title') | {{ config('app.name') }}</title>
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <base target="_blank" />

    <meta name="robots" content="noindex,nofollow">
    <meta name="googlebot" content="noindex,nofollow">

    <link rel="icon" href="{{ config('blog.favicon_url') }}">

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">

    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    {{-- <link href="{{ mix('css/all.css') }}" rel="stylesheet"> --}}

    @yield('header_scripts')

    <!-- Google Tag Manager -->
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', '{{ config('blog.google_tag_id') }}');
    </script>
    <!-- End Google Tag Manager -->
    <style type="text/css">
        html {
            height: 100%;
        }
    </style>
</head>

<body style="background-color: transparent;height:100%;">

    <a class="navbar-brand" href="{{ url('/') }}" style="position: absolute;top: 0;right: 0;z-index: 9999;">
        {{ config('app.name') }}
    </a>

    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ config('blog.google_tag_id') }}"
            height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <div id="app" style="height:100%;overflow: hidden;">
        @yield('above_container')
    </div>

    {{-- @include('cookie-consent::index') --}}
    {{-- <script src="{{ mix('js/manifest.js') }}" defer></script> --}}
    {{-- <script src="{{ mix('js/vendor.js') }}" defer></script> --}}
    <script src="{{ mix('js/app.js') }}" defer></script>
    @yield('footer_scripts')
</body>

</html>
