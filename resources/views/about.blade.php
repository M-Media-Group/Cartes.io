@extends('layouts.app')

@section('content')
<h1>About {{ config('app.name') }}</h1>

<p>This app is in beta testing and some information may be inaccurate. Help us by reporting incidents and spreading the word to your friends!</p>
<p>{{ config('app.name') }} keeps your data safe by encrypting sensitive passwords, not revealing personal data, and testing the security of your password through a database that lists millions of breached passwords: <a href="https://haveibeenpwned.com">haveibeenpwned.com</a>.</p>
<hr class="bg-dark"/>
<p>This is an open source project. Feel free to contribute to the development on <a href="https://github.com/mwargan/IncidentReport">GitHub</a>.</p>
<p>You may report incidents via this website (web interface) or via the API endpoints - available on request. You may develop automated tools that mark incidents at given locations based on real-world data triggers.</p>
<hr class="bg-dark"/>
<p>More info coming soon.</p>

@markdown

@endmarkdown

@endsection
