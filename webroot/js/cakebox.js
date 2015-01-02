/*
 * Global functions
 */
$(document).ready(function(){

	/*--------------------------------------------------
	 * Make modals draggable using jQuery UI
	 *------------------------------------------------*/
	$(".modal-dialog").draggable({
		handle: ".modal-header"
	});

	/*--------------------------------------------------
	 * Show Growl message for not-implemented-yet
	 *------------------------------------------------*/
	$('.todo').on('click', function () {
		$.msgGrowl ({
			type: 'error',
			title: 'Not Implemented',
			text: "Not implemented yet. Feel to submit a PR to speed up things."
		});
	});

	/*--------------------------------------------------
	 * Listen for generic ajax-file-modals
	 *------------------------------------------------*/
	$('.ajax-file-modal').on('click', function () {
		var modal = $('#ajaxModal')
		var title = S($(this).attr('id')).humanize().s
		var link = $(this).attr('rel')
		$('.modal-title').html(title)

		var jqxhr = $.getJSON(link, function(data) {
			modal.find('.modal-body').html('<pre>' + data.fileContent + '</pre>')
			modal.modal('show')
		})
		.fail(function() {
			ajaxFetchError()
		})
	})

});



/*--------------------------------------------------
 * Generic Growl message for failed ajax fetches.
 *------------------------------------------------*/
function ajaxFetchError(event) {
	$.msgGrowl ({
		type: 'error',
		title: 'Error fetching data',
		text: 'So sorry... something went wrong fetching the remote data'
	});
}
