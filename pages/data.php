<?

header("Content-Type: application/json");



if($_GET["graph"] == "last30"){
	$data = $app->getTripDataBetween(date("Y-m-d 00:00:00", time() - 6*7*24*60*60), date("Y-m-d 00:00:00", time() + 24*60*60), "week");
	$out = array();
	foreach($data as $d){
		$dtstr = $d->dt;
		$dtstr = substr($dtstr, 0, 4) . "-W" . substr($dtstr, 4, 2);
		$stamp = strtotime($dtstr) * 1000;
		if($_GET["data"] == "brakes"){
			$out[] = array($stamp, (int) $d->hard_accels + $d->hard_brakes);
		}else if($_GET["data"] == "speeding"){
			$out[] = array($stamp, (int) $d->duration_speeding / 60.0);
		}else if($_GET["data"] == "mpg"){
			$out[] = array($stamp, (int) $d->average_mpg);
		}else if($_GET["data"] == "distance"){
			$out[] = array($stamp, (int) $d->distance * 0.000621371); // convert to miles
		}else if($_GET["data"] == "fuel_cost"){
			$out[] = array($stamp, $d->fuel_cost);
		}
	}
	array_splice($out, count($out)-1, 1);
}else if($_GET["graph"] == "last7"){
	$data = $app->getTripDataBetween(date("Y-m-d 00:00:00", time() - 9*24*60*60), date("Y-m-d 00:00:00", time() + 24*60*60), "day");
	$out = array();
	foreach($data as $d){
		$dtstr = $d->dt;
		$stamp = strtotime($dtstr) * 1000;
		
/* 		echo $d->dt . " " . ($stamp/1000) . " gmt: " . gmdate("Y-m-d", $stamp/1000) . " normal: " . date("Y-m-d",$stamp/1000) . "\n"; */
		
		if($_GET["data"] == "brakes"){
			$out[] = array($stamp, (int) $d->hard_accels + $d->hard_brakes);
		}else if($_GET["data"] == "speeding"){
			$out[] = array($stamp, (int) $d->duration_speeding / 60.0);
		}else if($_GET["data"] == "mpg"){
			$out[] = array($stamp, (int) $d->average_mpg);
		}else if($_GET["data"] == "distance"){
			$out[] = array($stamp, (int) $d->distance * 0.000621371); // convert to miles
		}else if($_GET["data"] == "fuel_cost"){
			$out[] = array($stamp, $d->fuel_cost);
		}
	}
	
	/* array_splice($out, 0, 1); */
	
	array_splice($out, count($out)-1, 1);
}


print_r(json_encode($out));


?>