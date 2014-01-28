$(function(){
	var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

	function thisWeekArea(maxDt){
		var markings = [];
		var d = new Date(maxDt);
	
		// go to the first Saturday
	
		d.setUTCDate(d.getUTCDate() - 7); // get last week
		d.setUTCSeconds(0);
		d.setUTCMinutes(0);
		d.setUTCHours(0);
	
		var lastWeek = d.getTime();
	
		// when we don't set yaxis, the rectangle automatically
		// extends to infinity upwards and downwards
	
		return [{ xaxis: { from: lastWeek, to: maxDt } }];
	}
	
	function FourStatsGraph(graphId){
		var currSelectedTab = "brakes";
		var that = this;
		
		this.load = function(whichToLoad){
			$.ajax("http://drivesafetogether.com/?data=" + whichToLoad + "&graph=" + graphId, {
				success : function (data, status, xhr){
					if(currSelectedTab == whichToLoad){
						if(whichToLoad == "brakes"){
							$("#" + graphId + " p").text("Lower is Better! The number of hard brakes and fast accelerations:");
						}else if(whichToLoad == "mpg"){
							$("#" + graphId + " p").text("Higher is Better! The average miles per gallon achieved during the week:");
						}else if(whichToLoad == "distance"){
							$("#" + graphId + " p").text("Lower is Better! The total distance travelled in miles:");
						}else if(whichToLoad == "speeding"){
							$("#" + graphId + " p").text("Lower is Better! The number of minutes spent speeding (over 75 mph):");
						}else if(whichToLoad == "fuel_cost"){
							$("#" + graphId + " p").text("Lower is Better! Amount of hard-earned $$$ spent on fuel:");
						}
						
						$("#" + graphId + " h3 a").removeClass("active");
						$("#" + graphId + " h3 a." + whichToLoad).addClass("active");
						var tickValues = [];
						var currmin = new Date().getTime();
						var maxVal = 0;
						
						for(i=0;i<data.length;i++){
							var d = data[i];
							if(d[0] < currmin) currmin = d[0];
							if(d[1] > maxVal) maxVal = d[1];
							if(i > 0){
								tickValues.push((data[i][0] + data[i-1][0])/2);
							}
						}
						
						var currmax = currmin + 5 * 7 * 24 * 60 * 60 * 1000;
						var markings = thisWeekArea(currmax);

						// set the ticks as markings in the graph
						for(i=0;i<data.length;i++){
							var d = data[i];
							var tickMarking = { xaxis: { from: d[0], to: d[0] }, color: "#d0d0d0" };
							markings.push(tickMarking);
						}


						var options = {
							xaxis: {
								mode: "time",
								tickFormatter:function (val, axis) {
									var dt = new Date(val);
									var ndt = new Date(val + 6*24*60*60*1000);
									var nm = ndt.getUTCMonth() != dt.getUTCMonth() ? (months[ndt.getUTCMonth()] + " ") : "";
							        return months[dt.getUTCMonth()] + dt.getUTCDate() +  " - " + nm + ndt.getUTCDate();
							    },
								min: currmin,
								max: currmax,
								ticks: tickValues,
								tickLength: 0, // turn off ticks, we'll use markings for that
								labelWidth:120
							},
							yaxis: {
								labelWidth:40,
								reserveSpace: 0,
								min: - (maxVal * .05),
								minTickSize: 2,
								tickDecimals: 0,
								tickFormatter:function (val, axis) {
									if(whichToLoad == "fuel_cost"){
										return "$" + (Math.round(val));
									}
									return val;
							    },
							},
							grid: {
								markings: markings
							}
						};
						var plot = $.plot("#" + graphId + " .graph", [data], options);
					}
				},
				error : function(xhr, status, error){
					alert(status + ": " + error);
				}
			});
		}
		
		$("#" + graphId + " h3 a.brakes").click(function(){
			that.load("brakes");
			currSelectedTab = "brakes";
		});
		$("#" + graphId + " h3 a.speeding").click(function(){
			that.load("speeding");
			currSelectedTab = "speeding";
		});
		$("#" + graphId + " h3 a.mpg").click(function(){
			that.load("mpg");
			currSelectedTab = "mpg";
		});
		$("#" + graphId + " h3 a.distance").click(function(){
			that.load("distance");
			currSelectedTab = "distance";
		});
		$("#" + graphId + " h3 a.fuel_cost").click(function(){
			that.load("fuel_cost");
			currSelectedTab = "fuel_cost";
		});
	}
	
	var last30Days = new FourStatsGraph("last30");
	last30Days.load("brakes");

	var last30Days = new FourStatsGraph("last7");
	last30Days.load("brakes");

});