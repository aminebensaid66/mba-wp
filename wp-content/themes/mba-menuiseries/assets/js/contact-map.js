(function () {
	'use strict';
	document.querySelectorAll('[data-consent-map]').forEach(function (container) {
		var button = container.querySelector('[data-map-consent-trigger]');
		button.addEventListener('click', function () {
			var frame = document.createElement('iframe');
			frame.src = container.dataset.mapSrc;
			frame.title = 'Google Maps';
			frame.loading = 'lazy';
			frame.referrerPolicy = 'no-referrer-when-downgrade';
			frame.allowFullscreen = true;
			container.replaceChildren(frame);
		});
	});
}());
