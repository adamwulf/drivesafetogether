<?

header("Content-Type: application/json");

$data = $app->getTripDataBetween(date("Y-m-d 00:00:00", time() - 6*7*24*60*60), date("Y-m-d 00:00:00", time() + 24*60*60));



$out = array();
foreach($data as $d){
	$dtstr = $d->dt;
	$dtstr = substr($dtstr, 0, 4) . "-W" . substr($dtstr, 4, 2);
	$stamp = strtotime($dtstr);
	$out[] = array($stamp*1000, (int) $d->hard_accels);
}

/* array_splice($out, 0, 1); */
array_splice($out, count($out)-1, 1);


print_r(json_encode($out));

?>