<html>
<head>
<link href='http://fonts.googleapis.com/css?family=Duru+Sans' rel='stylesheet' type='text/css'>
<link href="<?=HOSTURL?>pages/style.css" rel="stylesheet" type="text/css" media="screen" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
<script src="//drivesafetogether.com/js/flot/jquery.flot.js"></script>
<script src="//drivesafetogether.com/js/flot/jquery.flot.resize.js"></script>
<script src="//drivesafetogether.com/js/flot/jquery.flot.time.js"></script>
</head>
<body>
<? include ROOT . "pages/analytics.php"; ?>
<header>
	<div id='logout'>
		<a href='<?=AUTOMATIC_REDIRECT_URI?>?logout'>Log Out</a>
	</div>
	<h1>Drive Safe Together</h1>
</header>
<section id="friends" class='clearfix'>
	<h2>Friends</h2>
	<div class='col3'>
		<h3>Scores</h3>
		<div id='facebook' class='roundedBox import'>
			which of your friends is winning highest scores this week
		</div>
	</div>
	<div class='col3'>
		<h3>Brakes/Accel</h3>
		<div id='twitter' class='roundedBox import'>
			fewest brake/accel/speeding warnings this week
		</div>
	</div>
	<div class='col3'>
		<h3>Friends</h3>
		<div id='allfriends' class='roundedBox'>
			<div id='twitter' class='import'>
				facebook and twitter import.<br>
				the list of all friends on dst
			</div>
		</div>
	</div>
</section>
<section id="this-week" class='clearfix'>
	<h2>This Week</h2>
	<div class='col23'>
		<h3>Score / Brakes,accel,speeding / MPG / distance</h3>
		<div class='roundedBox' style='overflow:auto;'>
<?
	$response = $app->automatic()->getTrips(1, 1);
	print_r($response);
?>
		</div>
	</div>
	<div class='col3 stat'>
		<h3>Today</h3>
		<div class='roundedBox'>
			Score:<br>
			brakes, accel, speeding:<br>
			MPG:<br>
			distance:
		</div>
	</div>
</section>
<section id="last-weeks" class='clearfix'>
	<h2>Last 30 days</h2>
	<h3>Score / Brakes,accel,speeding / MPG / distance</h3>
	<div class='roundedBox'>
		<div id="placeholder" class="graph"></div>
	</div>
</section>
<section id="your-car" class='clearfix'>
	<h2>Your Car</h2>
	<div class='col3'>
		<h3>MPG</h3>
		<div id='facebook' class='roundedBox import'>
			your rank of how good you do vs other drivers with your car
		</div>
	</div>
	<div class='col3'>
		<h3>Safety</h3>
		<div id='twitter' class='roundedBox import'>
			fewest brake/accel/speeding warnings this week
		</div>
	</div>
	<div class='col3'>
		<h3>Stats</h3>
		<div id='allfriends' class='roundedBox'>
			<div id='twitter' class='import'>
				how many miles you've driven with automatic.
				how safe you've been over time
				lifetime score / trend / something
			</div>
		</div>
	</div>
</section>
<footer>
	Copyright (c) 2014 Milestone Made, LLC<br>
	<script language="JavaScript">
					var name = "adam.wulf";
					var domain = "gmail.com";
					document.write("<a href=\"mailto:" + name + "@" + domain + "\">");
					document.write(name + "@" + domain + '</a>');
	</script>
</footer>
<script>
var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

function thisWeekArea(axes){
	var markings = [];
	var d = new Date(axes.xaxis.max);

	// go to the first Saturday

	d.setUTCDate(d.getUTCDate() - 7); // get last week
	d.setUTCSeconds(0);
	d.setUTCMinutes(0);
	d.setUTCHours(0);

	var lastWeek = d.getTime();

	// when we don't set yaxis, the rectangle automatically
	// extends to infinity upwards and downwards

	markings.push({ xaxis: { from: lastWeek, to: axes.xaxis.max } });

	return markings;
}

$.ajax("http://drivesafetogether.com/?data", {
	success : function (data, status, xhr){
		var tickValues = [];
		var currmin = new Date().getTime();
		for(i=0;i<data.length;i++){
			var d = data[i];
			if(d[0] < currmin) currmin = d[0];
			tickValues.push(d[0]);
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
				min: currmin - 1 * 1 * 24 * 60 * 60 * 1000,
				max: currmin + 5 * 7 * 24 * 60 * 60 * 1000,
				ticks: tickValues
			},
			grid: {
				markings: thisWeekArea
			}
		};
		var plot = $.plot("#placeholder", [data], options);
	},
	error : function(xhr, status, error){
		alert(status + ": " + error);
	}
})
</script>
</body>
</html>