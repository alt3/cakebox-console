$( document ).ready(function() {

	$('#fileModal').on('show.bs.modal', function (event) {
		var modal = $(this)
		var button = $(event.relatedTarget) // Button that triggered the modal
		var filename = button.closest('tr').find('td.filename').html()
		$('.modal-title').html('/etc/nginx/sites-available/' + filename)
		var jqxhr = $.getJSON( 'sitefiles/file/' + filename + '.json', function(data) {
			console.log(modal)
			modal.find('.modal-body').html('<pre>' + data.content + '</pre>')
		})
		.fail(function() {
			alert( 'So sorry, something went wrong fetching the file...' )
		})
	})

})
