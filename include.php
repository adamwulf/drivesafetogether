<?

define("ROOT", dirname(__FILE__) . "/");
define("LIBRARY", "library/");
define("SHOSTURL", "http://drivesafetogether.com/");
define("HOSTURL", "http://drivesafetogether.com");
define("DOMAIN", "drivesafetogether.com");
define("COOKIENAME", "drive");

putenv("TZ=GMT");

if(date("Z") != 0){
	throw new Exception("server is in wrong timezone: " . date("O") . ":" . date("Z"));
}

//define("GOOGLE_ANALYTICS", "UA-180672-3");

function getmicrotime(){ 
    return microtime(true); 
//    return ((float)$usec + (float)$sec); 
}

    

include_once ROOT . "include.classloader.php";
// include ROOT . "include.all.php";



header("Content-type: text/html; charset=UTF-8");

?>