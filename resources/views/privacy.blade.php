@extends('layouts.app')

@section('title', 'Privacy policy')

@section('content')
<h1>Privacy Policy</h1>

<h2>In plain ol' English</h2>
<p>Location information can be very private and sensitive. It's no joke, so that's why we don't store anything we don't need to, we ensure privacy by default, and we make all our code open-source.</p>
<ol>
<li>When you create a map and you are logged out, none of your personal information is stored on our servers.</li>
<li>When you create a marker/incident on the map and you are logged out, none of your personal information is stored on our servers.</li>
<li>When you are logged in, we associate any actions you take and rescources you make (like creating a map or a marker) with your account, so that you can access it at a later time and on other devices.</li>
<li>When you consent to analytics cookies, we share personally identifying info (such as your IP address) with Google Analytics, Facebook, and Hotjar. This data is NOT stored on our servers. We're not interested in your individual data, we look for usage patterns on the website which helps us continuously improve everyones experience.</li>
<li>If you do consent to analytics cookies, you can revoke your consent at any point afterwards. Just scroll down to the bottom of this page and press the "Revoke cookie" button.</li>
</ol>
<h2>More</h2>
<p>Your privacy is important to us. It is {{ config('app.name') }}'s policy to respect your privacy regarding any information we may collect from you across our website, <a href="{{ config('app.url') }}">{{ config('app.url') }}</a>, and other sites we own and operate.</p>
<p>We only ask for personal information when we truly need it to provide a service to you. We collect it by fair and lawful means, with your knowledge and consent. We also let you know why we’re collecting it and how it will be used.</p>
<p>We only retain collected information for as long as necessary to provide you with your requested service. What data we store, we’ll protect within commercially acceptable means to prevent loss and theft, as well as unauthorised access, disclosure, copying, use or modification.</p>
<p>We don’t share any personally identifying information publicly or with third-parties, except when required to by law.</p>
<p>Our website may link to external sites that are not operated by us. Please be aware that we have no control over the content and practices of these sites, and cannot accept responsibility or liability for their respective privacy policies.</p>
<p>You are free to refuse our request for your personal information, with the understanding that we may be unable to provide you with some of your desired services.</p>
<p>Your continued use of our website will be regarded as acceptance of our practices around privacy and personal information. If you have any questions about how we handle user data and personal information, feel free to contact us.</p>
<p>This policy is effective as of 23 January 2019.</p>
<revoke-consent-component></revoke-consent-component>
{{--  DOESN'T WORK FOR SOME REASON

 --}}
 @endsection
