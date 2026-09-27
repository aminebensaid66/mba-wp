(function () {
	'use strict';
	document.documentElement.classList.add('has-mba-faq-js');
	document.querySelectorAll('.mba-faq-item button').forEach(function (button) {
		button.addEventListener('click', function () {
			var panel = document.getElementById(button.getAttribute('aria-controls'));
			var expanded = button.getAttribute('aria-expanded') === 'true';
			button.setAttribute('aria-expanded', String(!expanded));
			panel.hidden = expanded;
		});
	});
}());
