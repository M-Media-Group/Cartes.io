<div class="js-cookie-consent cookie-consent alert bg-dark alert-dismissible" role="alert" style="position: fixed;bottom: 0;z-index: 99999;">

    <span class="cookie-consent__message">
        {!! trans('cookieConsent::texts.message') !!}
    </span>

    <button class="btn btn-primary js-cookie-consent-agree cookie-consent__agree">
        {{ trans('cookieConsent::texts.agree') }}
    </button>

	<button type="button" class="btn close" data-dismiss="alert" aria-label="Close">
		<span aria-hidden="true">&times;</span>
	</button>

</div>
