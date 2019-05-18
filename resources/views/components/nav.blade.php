<nav class="navbar navbar-expand-md navbar-dark navbar-laravel">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
{{--                 <img src="{{ config('blog.logo_url') }}" width="45" height="45" alt="{{config('app.name')}}">
 --}}                {{ config('app.name') }}
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
                        <a class="nav-link" href="/login">{{ __('Login') }}</a>
                    </li>
                @else
                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            {{ Auth::user()->username }} <span class="caret"></span>
                        </a>

                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                            @can('create', \App\Category::class)
                                <a class="dropdown-item" href="/categories/create">
                                    {{ __('Create category') }}
                                </a>
                            @endcan
                            @can('manage roles')
                                <a class="dropdown-item" href="/roles">
                                    {{ __('Manage roles') }}
                                </a>
                                <a class="dropdown-item" href="/roles/create">
                                    {{ __('Create roles') }}
                                </a>
                            @endcan
                            <a class="dropdown-item" href="/users/{{ Auth::user()->username }}/edit">
                                    {{ __('Account settings') }}
                                </a>
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
