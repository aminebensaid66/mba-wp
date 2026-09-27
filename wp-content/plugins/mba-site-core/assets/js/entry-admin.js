(function ($) {
	'use strict';
	var consent = $('[name="mba_publication_consent_confirmed"]');
	if (consent.length) {
		var featured = $('[name="mba_featured"]');
		var updateConsent = function () {
			featured.prop('disabled', !consent.prop('checked'));
			if (!consent.prop('checked')) {
				featured.prop('checked', false);
			}
		};
		consent.on('change', updateConsent);
		updateConsent();
	}
	$('.mba-entry-image-choose').on('click', function () {
		var field = $(this).closest('.mba-entry-image');
		var frame = wp.media({ title: mbaEntryAdmin.choose, library: { type: 'image' }, multiple: false });
		frame.on('select', function () {
			var item = frame.state().get('selection').first().toJSON();
			var thumb = item.sizes && item.sizes.thumbnail ? item.sizes.thumbnail.url : item.url;
			field.find('input').val(item.id);
			field.find('.mba-entry-image-preview').empty().append($('<img>').attr({ src: thumb, alt: '' }));
		});
		frame.open();
	});
	$('.mba-entry-image-remove').on('click', function () {
		var field = $(this).closest('.mba-entry-image');
		field.find('input').val('0');
		field.find('.mba-entry-image-preview').empty();
	});
})(jQuery);
