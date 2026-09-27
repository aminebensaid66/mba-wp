(function ($) {
	'use strict';

	function updateGallery(gallery) {
		var ids = gallery.find('li').map(function () { return $(this).data('id'); }).get();
		gallery.find('input[type="hidden"]').val(ids.join(','));
	}

	$('.mba-project-gallery').each(function () {
		var gallery = $(this);
		gallery.find('.mba-gallery-list').sortable({ update: function () { updateGallery(gallery); } });
	});

	$('.mba-project-gallery-choose').on('click', function () {
		var gallery = $(this).closest('.mba-project-gallery');
		var frame = wp.media({ title: mbaProjectAdmin.choose, library: { type: 'image' }, multiple: true });
		frame.on('open', function () {
			var selection = frame.state().get('selection');
			gallery.find('li').each(function () {
				selection.add(wp.media.attachment($(this).data('id')));
			});
		});
		frame.on('select', function () {
			var list = gallery.find('.mba-gallery-list').empty();
			frame.state().get('selection').each(function (attachment) {
				var item = attachment.toJSON();
				var thumb = item.sizes && item.sizes.thumbnail ? item.sizes.thumbnail.url : item.url;
				list.append($('<li>').attr('data-id', item.id)
					.append($('<img>').attr({ src: thumb, alt: '' }))
					.append($('<button>', { type: 'button', class: 'button-link-delete mba-project-gallery-remove', 'aria-label': mbaProjectAdmin.remove, text: '×' })));
			});
			updateGallery(gallery);
		});
		frame.open();
	});

	$(document).on('click', '.mba-project-gallery-remove', function () {
		var gallery = $(this).closest('.mba-project-gallery');
		$(this).closest('li').remove();
		updateGallery(gallery);
	});
})(jQuery);
