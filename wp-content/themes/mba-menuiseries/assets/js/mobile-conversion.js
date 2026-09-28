(function () {
	'use strict';
	document.addEventListener('click', function (event) {
		var action = event.target.closest('[data-mba-conversion]');
		if (!action) {
			return;
		}
		window.dispatchEvent(new window.CustomEvent('mba:conversion-action', {
			detail: {
				action: action.dataset.mbaConversion,
				contentType: action.dataset.mbaContentType
			}
		}));
	});
}());
