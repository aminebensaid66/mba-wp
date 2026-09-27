(function () {
	'use strict';
	var dialog = document.querySelector('[data-lightbox-dialog]');
	if (!dialog || typeof dialog.showModal !== 'function') return;
	var image = dialog.querySelector('[data-lightbox-image]');
	var caption = dialog.querySelector('[data-lightbox-caption]');
	var lastTrigger = null;
	document.querySelectorAll('[data-lightbox-open]').forEach(function (trigger) {
		trigger.addEventListener('click', function () {
			lastTrigger = trigger;
			image.src = trigger.dataset.fullSrc || '';
			image.alt = trigger.dataset.alt || '';
			caption.textContent = trigger.dataset.caption || '';
			dialog.showModal();
			dialog.querySelector('[data-lightbox-close]').focus();
		});
	});
	dialog.querySelector('[data-lightbox-close]').addEventListener('click', function () { dialog.close(); });
	dialog.addEventListener('click', function (event) { if (event.target === dialog) dialog.close(); });
	dialog.addEventListener('close', function () { image.removeAttribute('src'); if (lastTrigger) lastTrigger.focus(); });
}());
