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
	 * Listen for generic ajax file-modals
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

	/*--------------------------------------------------
	* Listen for generic ajax form-modals
	*------------------------------------------------*/
	$('.ajax-form-modal').on('click', function () {
		var modal = $($(this).attr('data-target'))
		var title = $(this).attr('alt')
		$('.modal-title').html(title)
		modal.modal('show')
	})

	/*--------------------------------------------------
	* Enable form submit using the Enter key
	*------------------------------------------------*/
	$('form').keypress(function(e) {
		if (e.keyCode == 13) {
			$(this).closest('.modal-content').find('#form-submit').trigger('click')
		}
	});

	/*--------------------------------------------------
	* Ajax (all) post form data. Includes CSRF token.
	*------------------------------------------------*/
	$('#form-submit').click(function() {
		var form = $(this).closest('.modal-content').find('form')
		var url = form.attr('action')
		var data = form.serialize();

		$.ajax({
			url: url,
			type: "POST",
			// headers: {
			// 	'X-CSRF-Token': $('input[name="_csrfToken"]').attr('value')
			// },
			data: data
		})
		.success(function( msg ) {
			alert( msg.message );
		})
		.fail(function( msg ) {
			var response = msg.responseJSON
			var inputs = form.find(":input")

			inputs.each(function(index, input){

				var formGroup = $('input[name="' + input.name + '"]').closest('.form-group')
				var feedback = formGroup.find('.form-control-feedback')
				var help = formGroup.find('.help-block')

				// remove previous feedback
				if (feedback.length) {
					feedback.remove()
				}
				if (help.length) {
					help.remove()
				}
				formGroup.removeClass('has-error has-feedback')

				// handle passed/failed validations
				var errors = response.errors[input.name]

				if (!errors) {
					formGroup.addClass('has-success has-feedback')
					formGroup.append('<span class="fa fa-check form-control-feedback" aria-hidden="true"></span>')
				}

				if (errors) {
					var list = ''
					$.each(errors, function (inputName, errorMessage) {
						list += '<li>' + errorMessage + '</li>'
					})
					formGroup.addClass('has-error has-feedback')
					formGroup.append('<span class="fa fa-times form-control-feedback" aria-hidden="true"></span>')
					formGroup.append('<span class="help-block"><ul class="list-unstyled">' + list + '</span>')
				}
			})
		})

	});



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
