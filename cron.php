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


$mysql = new MySQLConn(DATABASE_HOST, DATABASE_NAME, DATABASE_USER, DATABASE_PASS);
$db = new JSONtoMYSQL($mysql);
$app = new EasyApp($db);


$app->cronImportTrips(isset($_REQUEST["force"]) && $_REQUEST["force"]);

echo "done.";


?>