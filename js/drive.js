$(function(){
	var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
	var timezoneOffset =  + (new Date()).getTimezoneOffset()*60*1000;

	function thisWeekArea(maxDt, days, buffer){
		var markings = [];
		var lastWeek = maxDt - days/2.0*24*60*60*1000;
	
		// when we don't set yaxis, the rectangle automatically
		// extends to infinity upwards and downwards
	
		return [{ xaxis: { from: lastWeek, to: maxDt + buffer} }];
	}
	
	function FourStatsGraph(graphId, daysToMark){
		var currSelectedTab = "brakes";
		var that = this;
		
		this.load = function(whichToLoad){
			$.ajax("http://drivesafetogether.com/?data=" + whichToLoad + "&graph=" + graphId, {
				success : function (data, status, xhr){
					if(currSelectedTab == whichToLoad){
						if(whichToLoad == "brakes"){
							$("#" + graphId + " p.graphDesc").text("Lower is Better! The number of hard brakes and fast accelerations:");
						}else if(whichToLoad == "mpg"){
							$("#" + graphId + " p.graphDesc").text("Higher is Better! The average miles per gallon achieved during the week:");
						}else if(whichToLoad == "distance"){
							$("#" + graphId + " p.graphDesc").text("Lower is Better! The total distance travelled in miles:");
						}else if(whichToLoad == "speeding"){
							$("#" + graphId + " p.graphDesc").text("Lower is Better! The number of minutes spent speeding (over 75 mph):");
						}else if(whichToLoad == "fuel_cost"){
							$("#" + graphId + " p.graphDesc").text("Lower is Better! Amount of hard-earned $$$ spent on fuel:");
						}
						
						$("#" + graphId + " h3 a").removeClass("active");
						$("#" + graphId + " h3 a." + whichToLoad).addClass("active");


						var tickValues = [];
						var currmin = new Date().getTime();
						var currmax = 0;
						var maxVal = 0;
						
						for(i=0;i<data.length;i++){
							var d = data[i];
							if(d[0] < currmin) currmin = d[0];
							if(d[0] > currmax) currmax = d[0];
							if(d[1] > maxVal) maxVal = d[1];
							tickValues.push(data[i][0]);
						}
						
						var maxDt = new Date(0);
						maxDt.setUTCMilliseconds(currmax);
						if(maxDt.getDay() > 0){
							var daysToAdd = 7 - maxDt.getDay();
							currmax += daysToAdd * 24 * 60 * 60 * 1000;
						}
						maxDt = new Date(0);
						maxDt.setUTCMilliseconds(currmax);
/* 						alert(graphId + "\n" + currmax + "\n" + maxDt + "\n" + maxDt.getDay()); */
						

						
						var buffer = 0;
						if(daysToMark > 1){
							buffer = 2*24*60*60*1000;
						}else{
							buffer = 8*60*60*1000;
						}
						
						var markings = thisWeekArea(currmax, daysToMark, buffer);

						currmin -= buffer;
						currmax += buffer;

						// set the ticks as markings in the graph
						for(i=1;i<data.length;i++){
							var mark = (data[i][0] + data[i-1][0]) / 2;
							var tickMarking = { xaxis: { from: mark, to: mark }, color: "#d0d0d0" };
							markings.push(tickMarking);
						}

						var options = {
							series: {
						        lines: { show: true},
						        points: { show: true, fill: true }
						    },
							xaxis: {
								mode: "time",
								tickFormatter:function (val, axis) {
									var dt = new Date(val);
									if(daysToMark > 1){
										var pdt = new Date(0);
										pdt.setUTCMilliseconds(val);
										var ndt = new Date(0);
										ndt.setUTCMilliseconds(val + 6*24*60*60*1000);
										var nm = ndt.getUTCMonth() != pdt.getUTCMonth() ? (months[ndt.getUTCMonth()] + " ") : "";
								        return months[pdt.getUTCMonth()] + pdt.getUTCDate() +  " - " + nm + ndt.getUTCDate();
									}else{
										// since the tick label is teh average of two dates,
										// this will show "yesterday", so add a day to move it to today
										var dt = new Date(0);
										dt.setUTCMilliseconds(val);
								        return months[dt.getUTCMonth()] + dt.getUTCDate();
									}
							    },
								min: currmin,
								max: currmax,
								ticks: tickValues,
								tickLength: 0
							},
							yaxis: {
								labelWidth:40,
/* 								reserveSpace: 0, */
								min: - (maxVal * .05),
/* 								minTickSize: 2, */
								tickDecimals: 2,
								tickFormatter:function (val, axis) {
									if(whichToLoad == "fuel_cost"){
										if(maxVal > 2){
										return "$" + Math.round(val);
										}
										return "$" + val.toFixed(2);
									}
									return val;
							    }
							}
							,
							grid: {
								markings: markings,
								borderColor: "#697279"
							}
						};
						var plot = $.plot("#" + graphId + " .graph", [data], options);
					}
				},
				error : function(xhr, status, error){
					// fail silently
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
	
	
	function TodayLoader(){
		var that = this;
		
		this.load = function(whichToLoad){
			$.ajax("http://drivesafetogether.com/?data=today", {
				success : function (data, status, xhr){
					
				},
				error : function(xhr, status, error){
					// fails 
				}
			});
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	var last30Days = new FourStatsGraph("last30", 7);
	last30Days.load("brakes");

	var last30Days = new FourStatsGraph("last7", 1);
	last30Days.load("brakes");

});