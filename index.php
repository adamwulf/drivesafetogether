<?
error_reporting(E_ALL);

include "include.php";
include "functions.php";
include "config.php";
include('automatic-php-api/PHP-OAuth2/Client.php');
include('automatic-php-api/PHP-OAuth2/GrantType/IGrantType.php');
include('automatic-php-api/PHP-OAuth2/GrantType/AuthorizationCode.php');


$classLoader->addToClasspath(ROOT . "json-to-mysql/");
$classLoader->addToClasspath(ROOT . "automatic-php-api/Automatic/");

// start session for tracking logged in user
session_start();

// create global objects

$mysql = new MySQLConn(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASS);
$db = new JSONtoMYSQL($mysql);
$app = new EasyApp($db);



if(isset($_GET["logout"])){
	$app->logout();
    header('Location: ' . AUTOMATIC_REDIRECT_URI);
    die();
}else if (isset($_GET['code'])) {
	$app->validateLoginForCode($_GET["code"]);    
    header('Location: ' . AUTOMATIC_REDIRECT_URI);
    die('Redirect');
}else if(isset($_GET['automatic_login'])){
	$scopes = array("scope:location", "scope:vehicle", "scope:trip:summary");
	$auth_url = $app->automatic()->authenticationURLForScopes($scopes);
    header('Location: ' . $auth_url);
    die('Redirect');
}


if($app->isLoggedIn()){
	include "pages/dashboard.php";
}else{
	include "pages/login.php";
}

?>