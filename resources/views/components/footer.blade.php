<div class="alert alert-primary" role="alert">
<b>Incident Tracker</b> will be rebranded as <b>Cartes</b> and moved to <a href="https://cartes.io">cartes.io</a> on the <b>8th of May</b>.
</div>
		@include('cookieConsent::index')

		<script src="{{ mix('js/manifest.js') }}" defer></script>

		<script src="{{ mix('js/vendor.js') }}" defer></script>

		<script src="{{ mix('js/app.js') }}" defer></script>
   		@yield('footer_scripts')
	</body>
</html>
