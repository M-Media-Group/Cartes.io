@extends('layouts.app')

@section('title', 'About')

@section('content')
    <h1>About {{ config('app.name') }}</h1>

    <p>This web-app allows you to create maps for any purpose safely and anonymously. You can easily then engage with your
        map via the interface, API, or embed.</p>
    <p>{{ config('app.name') }} keeps your data safe by encrypting sensitive passwords, not revealing personal data, and
        testing the security of your password through a database that lists millions of breached passwords: <a
            href="https://haveibeenpwned.com">haveibeenpwned.com</a>.</p>
    <p>When you create maps and markers anonymously, the map / marker ID and associated secret token is stored in your
        browser local-storage. Only your browser will be able to edit / delete maps and markers you create.</p>
    <hr class="bg-dark" />
    <p>This is an open source project. Feel free to contribute to the development on <a
            href="https://github.com/M-Media-Group/Cartes.io">GitHub</a>.</p>
    <p>You may create maps and markers via this website (web interface) or via the API endpoints. You may develop automated
        tools that create markers at given locations based on real-world data triggers. Be mindful of the rate-limiter and
        don't try to beat it.</p>
    <hr class="bg-dark" />
    <p>M Media is the founder and lead developer team on this project; if you need to, you can contact us <a
            href="https://mmediagroup.fr/contact" rel="noopener" target="_BLANK">here</a>.</p>

    @markdown

    @endmarkdown

@endsection
