(function ($) {
	'use strict';

	function updateGalleryInput() {
		var ids = $('.mba-gallery-list li').map(function () { return $(this).data('id'); }).get();
		$('#mba_gallery').val(ids.join(','));
	}

	$('.mba-gallery-list').sortable({ update: updateGalleryInput });

	$(document).on('click', '.mba-gallery-remove', function () {
		$(this).closest('li').remove();
		updateGalleryInput();
	});

	$('.mba-gallery-choose').on('click', function () {
		var frame = wp.media({ title: mbaProductAdmin.galleryTitle, library: { type: 'image' }, multiple: true });
		frame.on('select', function () {
			var list = $('.mba-gallery-list').empty();
			frame.state().get('selection').each(function (attachment) {
				var item = attachment.toJSON();
				var thumb = item.sizes && item.sizes.thumbnail ? item.sizes.thumbnail.url : item.url;
				list.append($('<li>').attr('data-id', item.id).append($('<img>').attr({ src: thumb, alt: '' })).append($('<button>', { type: 'button', class: 'button-link-delete mba-gallery-remove', 'aria-label': mbaProductAdmin.removeImage, text: '×' })));
			});
			updateGalleryInput();
		});
		frame.open();
	});

	$('.mba-document-choose').on('click', function () {
		var frame = wp.media({ title: mbaProductAdmin.pdfTitle, library: { type: 'application/pdf' }, multiple: false });
		frame.on('select', function () {
			var item = frame.state().get('selection').first().toJSON();
			$('#mba_technical_document').val(item.id);
			$('.mba-document-label').text(item.title || item.filename);
		});
		frame.open();
	});

	$('.mba-document-remove').on('click', function () {
		$('#mba_technical_document').val('');
		$('.mba-document-label').text(mbaProductAdmin.noPdf);
	});

	$(document).on('click', '.mba-choose-finish-image', function () {
		var row = $(this).closest('.mba-finish-row');
		var frame = wp.media({ title: mbaProductAdmin.finishTitle, library: { type: 'image' }, multiple: false });
		frame.on('select', function () {
			var item = frame.state().get('selection').first().toJSON();
			var thumb = item.sizes && item.sizes.thumbnail ? item.sizes.thumbnail.url : item.url;
			row.find('.mba-finish-image-id').val(item.id);
			row.find('.mba-finish-preview').html($('<img>').attr({ src: thumb, alt: '' }));
		});
		frame.open();
	});

	$(document).on('click', '.mba-remove-row', function () {
		var row = $(this).closest('.mba-repeater-row');
		if ( row.siblings('.mba-repeater-row').length ) {
			row.remove();
		} else {
			row.find('input').val('');
			row.find('img').remove();
		}
	});

	$('.mba-add-row').on('click', function () {
		var rows = $(this).siblings('.mba-repeater-rows');
		var row = rows.children('.mba-repeater-row').last().clone();
		row.find('input').val('');
		rows.append(row);
	});

	$('.mba-add-performance').on('click', function () {
		var rows = $('.mba-performance-rows');
		var row = rows.children('.mba-performance-row').last().clone();
		row.find('input').val('');
		rows.append(row);
	});

	$('.mba-add-finish').on('click', function () {
		var rows = $('.mba-finish-rows');
		var row = rows.children('.mba-finish-row').last().clone();
		row.find('input').val('');
		row.find('.mba-finish-preview').empty();
		rows.append(row);
	});
})(jQuery);
