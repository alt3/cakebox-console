$(function () {

	var data = [
		{label: "CakePHP", data: 4},
		{label: "Laravel", data: 1}
	];

	$.plot($("#donut-chart"), data,
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
		}
	});

});
