(function () {
	'use strict';
	var header = document.querySelector('header.wp-block-template-part');
	if (!header) {
		return;
	}
	var measure = function () {
		document.documentElement.style.setProperty('--mba-header-height', header.getBoundingClientRect().height + 'px');
	};
	measure();
	if (typeof ResizeObserver !== 'undefined') {
		new ResizeObserver(measure).observe(header);
	} else {
		window.addEventListener('resize', measure);
	}
})();
