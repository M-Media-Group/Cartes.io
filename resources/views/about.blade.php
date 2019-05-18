@extends('layouts.app')

@section('content')
<h1>About {{ config('app.name') }}</h1>

<p>This app is in beta testing and some information may be inaccurate. Help us by reporting icidents and spreading the word to your friends!</p>
<p>More info coming soon.</p>

@markdown

@endmarkdown

@endsection
