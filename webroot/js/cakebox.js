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
	$('.todo').on('click', function (e) {
		$.msgGrowl ({
			type: 'error',
			title: 'Not Implemented',
			text: "Not implemented yet. Feel to submit a PR to speed up things."
		});
	});

});
