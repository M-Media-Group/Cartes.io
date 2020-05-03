<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
     @include('components.head', [ 'index_and_googlebots' => isset($index_and_googlebots) ? $index_and_googlebots : true ])
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
            <div class="row justify-content-center mt-5">

                <div class="col-md-12" style="max-width: 950px;">
                    @yield('content')
                </div>

            </div>
        </div>
        <hr/>
        <div class="footer d-flex justify-content-center">
            <small class="mb-3">
                <a href="/login" class="text-muted">Login</a>
                <a href="/about" class="text-muted">About</a>
                <a href="/privacy-policy" class="text-muted">Privacy policy</a>
                <a class="text-muted" href="/terms-and-conditions">Terms and conditions</a>
            </small>
        </div>
    </div>
@include('components.footer')
