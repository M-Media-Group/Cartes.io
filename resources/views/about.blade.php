@extends('layouts.app')

@section('content')
<h1>About {{ config('app.name') }}</h1>

<p>This app is in beta testing and some information may be inaccurate. Help us by reporting incidents and spreading the word to your friends!</p>
<p>{{ config('app.name') }} keeps your data safe by encrypting sensitive passwords, not revealing personal data, and testing the security of your password through a database that lists millions of breached passwords: <a href="https://haveibeenpwned.com">haveibeenpwned.com</a>.</p>
<p>More info coming soon.</p>

@markdown

@endmarkdown

@endsection
