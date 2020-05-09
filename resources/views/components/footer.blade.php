
		@include('cookieConsent::index')

		<script src="{{ mix('js/manifest.js') }}" ></script>

		<script src="{{ mix('js/vendor.js') }}" ></script>

		<script src="{{ mix('js/app.js') }}" ></script>
   		@yield('footer_scripts')
	</body>
</html>
