
fallback.ready(function() {

	// Ajax load status tab
	$('#tab-status a').click(function (e) {
		currentPanel = $(this).attr('href')
		if ($(currentPanel).has('.ajax-loader').length != 0 ) {
			loadTabStatus()
		}
	})

	// Ajax load software tab
	$('#tab-software a').click(function (e) {
		currentPanel = $(this).attr('href')
		if ($(currentPanel).has('.ajax-loader').length != 0 ) {
			loadTabSoftware()
		}
	})

});

/*
* Ajax load Status tab
*
* @todo Make generic
*/
function loadTabStatus() {
	var jqxhr = $.getJSON( '../dashboards/checks.json', function(data) {
		// loop through each category
		$.each( data, function( category, checks ) {
			failCount = 0

			// generate <li> items for each check in the current category
			$.each( data[category], function( index, check ) {
				if (check.pass == true ) {
					icon = '<i class="fa fa-check"></i>'
					$('#status-' + category + ' ul').append( '<li class="check">' + icon + check.message + '</li>')
				} else {
					icon = '<i class="fa fa-times"></i>'
					$('#status-' + category + ' ul').append( '<li class="check text-danger">' + icon + check.message + '</li>')
					failCount++
				}
			});

			// set panel header to danger for failed category
			if (failCount != 0) {
				$('#status-' + category).removeClass('panel-primary')
				$('#status-' + category).addClass('danger')
			}
		});
	})
	.done(function() {
		$('#panel-status .ajax-loader').html('').remove()
		$('#panel-status .widget').removeClass('hidden')
	})
	.fail(function() {
		alert( 'So sorry, something went wrong fetching checks' )
	})
}

/**
* Ajax load Software tab
*/
function loadTabSoftware() {
	var jqxhr = $.getJSON( '../dashboards/software.json', function(data) {
		console.dir(data)

		// fill OS panel
		$('.os-description').append('<a href="https://wiki.ubuntu.com/LTS">' + data['operating_system']['DISTRIB_DESCRIPTION'] + '</a>')
		$('.os-codename').append(data['operating_system']['DISTRIB_CODENAME'])
		$('.os-architecture').append(data['operating_system']['architecture'])

		// fill Software panel
		$.each(data['packages'], function( index, column ) {
			var target = $('.panel-body.packages > .row')
			var col = $('<div class="col-sm-3" id="packages-column-' + index + '" />').appendTo(target)
			var list = $('<ul class="list-unstyled" />').appendTo('#packages-column-' + index)

			// one LI per package
			$.each(column, function(index, package) {
				li = '<li class="package">'
				if (package.version) {
					li += '<a href="' + package.link + '">' + package.name + '&nbsp;' + package.version + '</a>'
				} else {
					li += '<a href="' + package.link + '">' + package.name + '</a> <i class="fa fa-times" title="Could not detect version"></i>'
				}
				li += '</li>'
				list.append(li)
			})
		})

		// fill PHP Modules panel
		$.each(data['php_modules'], function( index, column ) {
			var target = $('.panel-body.php-modules > .row')
			var col = $('<div class="col-sm-3" id="php-modules-column-' + index + '" />').appendTo(target)
			var list = $('<ul class="list-unstyled" />').appendTo('#php-modules-column-' + index)

			// one LI per package
			$.each(column, function(index, phpmodule) {
				li = '<li class="php-module">'
				if (phpmodule['link']) {
					li += '<a href="' + phpmodule.link + '">' + phpmodule.name + '</a>'
				} else {
					li += phpmodule.name
				}
				li += '</li>'
				list.append(li)
			})
		})

		// fill nginx core-modules column
		$.each(data['nginx_modules']['core'], function( index, module ) {
			var list = $('#nginx-core-modules > ul')
			list.append('<li><a href="' + module.link + '">' + module.short_name + '</a>')
		})

		// fill nginx 3rd-party-modules column
		$.each(data['nginx_modules']['3rdparty'], function( index, module ) {
			var list = $('#nginx-3rdparty-modules > ul')
			list.append('<li><a href="' + module.link + '">' + module.short_name + '</a>')
		})

	})
	.done(function() {
		$('#panel-software .ajax-loader').html('').remove()
		$('#panel-software .widget').removeClass('hidden')
	})
	.fail(function() {
		alert( 'So sorry, something went wrong fetching software' )
	})
}
