$(document).ready(function(){

	/*--------------------------------------------------
	 * Load the donut using the "data" variable set in
	 * the Dashboards index view.
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
