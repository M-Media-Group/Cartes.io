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
            <div class="row justify-content-center mt-5">

                <div class="col-md-12">
                    @yield('content')
                </div>

            </div>
        </div>
        <div class="footer">
        </div>
    </div>
</body>
</html>
