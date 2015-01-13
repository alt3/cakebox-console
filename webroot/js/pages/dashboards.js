$(document).ready(function(){

	/*--------------------------------------------------
	 * Load the flot donut using the "data" variable set
	 * in the Dashboards index view
	 *
	 * http://www.flotcharts.org
	 *------------------------------------------------*/
	$.plot($("#donut-chart"), donutData,
	{
		colors: ["#F90", "#222", "#777", "#AAA"],
		series: {
			pie: {
				innerRadius: 0.5,
				show: true
			}
		},
		grid: {
			hoverable: true
		},
		tooltips: true
	});

	/*--------------------------------------------------
	 * Allow closing the sponsor widget
	 *------------------------------------------------*/
	$("#close-sponsors").click(function() {
		$( ".widget.sponsors" ).slideUp()
	});

});
