/*
 * Status tab click event
 *
 * @todo 1 => make generic
 * @todo 2 => move into JS blocks
 */
$(document).ready(function(){

	$('#tab-status a').click(function (e) {
		currentPanel = $(this).attr('href')
		if ($(currentPanel).has('.ajax-loader').length != 0 ) {
			loadTabStatus()
		}
	})

	$('#tab-credits a').click(function (e) {
		currentPanel = $(this).attr('href')
		if ($(currentPanel).has('.ajax-loader').length != 0 ) {
			loadTabCredits()
		}
	})

});

/*
 * Draft code for ajax-loading checks panel using Dashboards controller
 *
 * @todo Make generic
 */
function loadTabStatus() {
	var jqxhr = $.getJSON( 'dashboards/checks.json', function(data) {
		// loop through each category
		$.each( data, function( category, checks ) {
			failCount = 0

			// generate <li> items for each check in the current category
			$.each( data[category], function( index, check ) {
				if (check.pass == true ) {
					icon = '<i class="fa fa-check"></i>'
					$('#status-' + category + ' ul').append( '<li>' + icon + check.message + '</li>')
				} else {
					icon = '<i class="fa fa-times"></i>'
					$('#status-' + category + ' ul').append( '<li class="text-danger">' + icon + check.message + '</li>')
					failCount++
				}
			});

			// set panel header to danger for failed category
			if (failCount != 0) {
				$('#status-' + category).removeClass('panel-primary')
				$('#status-' + category).addClass('panel-danger')
			}
		});
	})
	.done(function() {
		$('#panel-status .ajax-loader').html('').remove()
		$('#panel-status .panel-content').removeClass('hidden')
	})
	.fail(function() {
		alert( 'So sorry, something went wrong fetching checks' )
	})
}

/**
 * Ajax loads Credits tab
 */
function loadTabCredits() {
	var jqxhr = $.getJSON( 'dashboards/contributors.json', function(data) {
		$.each(data['contributors'], function( index, column ) {

			// one UL per column
			var target = $('.panel-body.credits > .row')
			var col = $('<div class="col-sm-4" id="credits-column-' + index + '" />').appendTo(target)
			var list = $('<ul class="list-unstyled" />').appendTo('#credits-column-' + index)

			// one LI per contributor
			$.each(column, function(index, contributor) {
			 	li = '<li class="contributor"><span>'
			 	li += '<img src="' + contributor.author.avatar_url + '&size=20" alt=""></img>'
			 	li += '<a href="' + contributor.author.html_url + '" title="' + contributor.total + ' commits">'
			 	li += contributor.author.login
			 	li += '</a>'
			 	li += '</span></li>'
			 	list.append(li)
			})
		})
	})
	.done(function() {
		$('#panel-credits .ajax-loader').html('').remove()
		$('#panel-credits .panel-content').removeClass('hidden')
	})
	.fail(function() {
		alert( 'So sorry, something went wrong fetching checks' )
	})
}
