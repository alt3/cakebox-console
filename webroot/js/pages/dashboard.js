/*==================================================================

  [Page Specific Script]

	=> /sitefiles

  [Table of Contents]

	1. Generate Flot Donut Chart
	2. Sponsors Widget Close Listener

===================================================================*/




/*------------------------------------------------------------------
 * 1. Generate Flot Donut Chart
 *
 * Generate the flor donut using "donutData" inline Javascript variable
 * set in the Dashboards index view (http://www.flotcharts.org)
 * ---------------------------------------------------------------*/
$(document).ready(function() {
    //console.dir(donutData)

    $.plot(
        $("#donut-chart"),
        donutData,
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
        }
    )
})

/*------------------------------------------------------------------
 * 2. Sponsors Widget Close Listener
 * ---------------------------------------------------------------*/
$(document).ready(function() {
    $("#close-sponsors").click(function() {
        $(".widget.sponsors").slideUp()
    })
})
