/*------------------------------------------------------------------

  [Globally Applied Application Script]

  [Table of Contents]

	1. Make Bootstrap Modals Draggable
	2. Make Bootstrap Alerts Hideable
	3. MsgGrowl: Not Implemented Yet
	4. Generic Ajax File Modal Listener
	5. Generic Ajax Form Modal Listener
	6. Generic Ajax Form Poster
	7. Remove Form Validation Feedback
	8. All Form Validation Feedback To Success
	9. Allow Form Submit Using Enter Key
	10. MsgGrowl: Generic Ajax Fetch Errors

-------------------------------------------------------------------*/




/*------------------------------------------------------------------
* 1. Make Bootstrap Modals Draggable (using jQuery-UI)
* ---------------------------------------------------------------*/
$(document).ready(function() {
	$(".modal-dialog").draggable({
		handle: ".modal-header"
	})
})

/*------------------------------------------------------------------
 * 2. Make Bootstrap Alerts Hideable
 *
 * Use jQuery for hiding the alerts on close instead of removing
 * them from DOM completely (as done by Bootstrap) to allow re-use.
 * ---------------------------------------------------------------*/
$(document).ready(function() {
	$('.alert .close').on('click',function(){
		$(this).parent().hide();
	})
})

/*------------------------------------------------------------------
 * 3. MsgGrowl: Not Implemented Yet
 * ---------------------------------------------------------------*/
$(document).ready(function() {
	$('.todo').on('click', function () {
		$.msgGrowl ({
			type: 'error',
			title: 'Not Implemented Yet',
			text: "Feel free to submit a PR to speed things up."
		})
	})
})

/*------------------------------------------------------------------
 * 4. Generic Ajax File Modal Listener
 * ---------------------------------------------------------------*/
$(document).ready(function() {
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
})

/*------------------------------------------------------------------
 * 5. Generic Ajax Form Modal Listener
 * ---------------------------------------------------------------*/
$(document).ready(function() {
	$('.ajax-form-modal').on('click', function () {
		var modal = $($(this).attr('data-target'))
		var title = $(this).attr('alt')
		$('.modal-title').html(title)
		modal.modal('show')
	})
})

/*------------------------------------------------------------------
 * 6. Generic Ajax Form Poster (including CSRF token)
 * ---------------------------------------------------------------*/
$(document).ready(function() {
	$('#form-submit').click(function() {
		var form = $(this).closest('.modal-content').find('form')
		var url = form.attr('action')
		var data = form.serialize();

		$.ajax({
			url: url,
			type: "POST",
			// Note => explicit X-CSRF header disabled for now because we are
			//         passing the token as part of the form fields as well.
			// headers: {
			// 	'X-CSRF-Token': $('input[name="_csrfToken"]').attr('value')
			// },
			data: data
		})
		.fail(function( msg ) {
			var response = msg.responseJSON
			var validationErrors = response.validation_errors
			clearFormFeedback(form)

			// Display error message only if all fields validated
			if (!validationErrors) {
				setFormToValidated(form)
				form.find('.alert > span').html(msg.responseJSON.message)
				form.find('.alert').show()
				return
			}

			// One or more fields did not pass validation
			var inputs = form.find(".form-group.has-feedback :input")
			$.each(inputs, function (index, input) {
				var formGroup = $('input[name="' + input.name + '"]').closest('.form-group')

				// set to success if no validation errors are set
				if(!validationErrors[input.name]) {
					formGroup.addClass('has-success')
					formGroup.append('<span class="fa fa-check form-control-feedback" aria-hidden="true"></span>')
					return true
				}

				// set validation errors
				var list = ''
				$.each(validationErrors[input.name], function (validationRule, validationMessage) {
					list += '<li>' + validationMessage + '</li>'
				})
				formGroup.addClass('has-error')
				formGroup.append('<span class="fa fa-times form-control-feedback" aria-hidden="true"></span>')
				formGroup.append('<span class="help-block"><ul class="list-unstyled">' + list + '</span>')

			})
		})
		.success(function( msg ) {
			var target = $('.index-main .alert span.message')
			target.html(msg.message)
			target.closest('div').show()
			$('.modal.in').modal('hide')
		})
	})
})

/*------------------------------------------------------------------
 * 7. Remove Form Validation Feedback
 * ---------------------------------------------------------------*/
function clearFormFeedback(form) {
	form.find(".form-group.has-feedback :input").each( function(index, input) {
	//form.find(":input.has-feedback").each( function(index, input) {
		var formGroup = $('input[name="' + input.name + '"]').closest('.form-group')
		formGroup.removeClass('has-error has-success')
		formGroup.find('.form-control-feedback').remove()
		formGroup.find('.help-block').remove()

	})
}

/*------------------------------------------------------------------
 * 8. All Form Validation Feedback To Success
 * ---------------------------------------------------------------*/
function setFormToValidated(form) {
	form.find(".form-group.has-feedback :input").each( function(index, input) {
		var formGroup = $('input[name="' + input.name + '"]').closest('.form-group .has-feedback')
		//formGroup.addClass('has-feedback has-success')
		formGroup.addClass('has-success')
		formGroup.append('<span class="fa fa-check form-control-feedback" aria-hidden="true"></span>')
	})
}

/*------------------------------------------------------------------
 * 9. Allow Form Submit Using Enter Key
 * ---------------------------------------------------------------*/
$(document).ready(function() {
	$('form').keypress(function(e) {
		if (e.keyCode == 13) {
			$(this).closest('.modal-content').find('#form-submit').trigger('click')
		}
	})
})

/*------------------------------------------------------------------
 * 10. MsgGrowl: Generic Ajax Fetch Errors
 * ---------------------------------------------------------------*/
function ajaxFetchError(event) {
	$.msgGrowl ({
		type: 'error',
		title: 'Error fetching data',
		text: 'So sorry... something went wrong fetching the remote data'
	});
}