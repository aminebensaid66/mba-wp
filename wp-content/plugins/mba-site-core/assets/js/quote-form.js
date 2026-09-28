(function () {
	'use strict';
	document.querySelectorAll('.mba-quote-form').forEach(function (form) {
		form.addEventListener('submit', function () {
			var button = form.querySelector('[type="submit"]');
			if (button && form.checkValidity()) {
				button.disabled = true;
				button.setAttribute('aria-disabled', 'true');
			}
		});
	});
}());
