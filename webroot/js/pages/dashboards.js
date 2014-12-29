$(document).ready(function(){

	// load the donut using "data" variable set in the Dashboards index view
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

});
