
		@include('cookieConsent::index')

		<script src="{{ mix('js/manifest.js') }}" defer></script>

		<script src="{{ mix('js/vendor.js') }}" defer></script>

		<script src="{{ mix('js/app.js') }}" defer></script>
   		@yield('footer_scripts')
	</body>
</html>
