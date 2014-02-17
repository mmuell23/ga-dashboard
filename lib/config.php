<?php

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__).'/google-api-php-client/src/');
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

require_once dirname(__FILE__).'/google-api-php-client/src/Google/Client.php';
require_once dirname(__FILE__).'/google-api-php-client/src/Google/Service/Analytics.php';
require_once dirname(__FILE__).'/class.report.php';
require_once dirname(__FILE__).'/class.visit.report.php';
require_once dirname(__FILE__).'/constants.php';

$con = mysql_connect("localhost","root","root") or die("no connect 1");
$db = mysql_select_db("dashboard") or die("no connect 2");

session_start();

//set initial session
if(!isset($_SESSION["from"])) {
	$_SESSION["from"] = date("Y-m-d");
	$_SESSION["to"] = date("Y-m-d");
}

define("DATE_FORMAT", "d.m.Y");

$scriptUri = "http://".$_SERVER["HTTP_HOST"].$_SERVER['PHP_SELF'];

$analytics = false;
$client = new Google_Client();
$client->setApplicationName('VRM');

// Visit https://cloud.google.com/console to generate your
// client id, client secret, and to register your redirect uri.
$client->setClientId(CLIENT_ID);
$client->setClientSecret(CLIENT_SECRET);
$client->setRedirectUri($scriptUri);
//$client->setDeveloperKey('AIzaSyD2CwqRvK9hGy6aplKMeFH_gbug1AuhodU');
$client->setScopes(array('https://www.googleapis.com/auth/analytics.readonly'));

// Magic. Returns objects from the Analytics Service instead of associative arrays.
//$client->setUseObjects(true);

if (isset($_SESSION['token'])) {
  $client->setAccessToken($_SESSION['token']);
}

if (isset($_GET['code'])) {
  $client->authenticate($_GET['code']);
  $_SESSION['token'] = $client->getAccessToken();
  $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
  header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
}


if ($client->getAccessToken() && !$client->isAccessTokenExpired()) {
  $analytics = new Google_Service_Analytics($client);	
  DashReport::setAnalytics($analytics);
  $_SESSION["ga_connected"] = true;
} else {
	$_SESSION["ga_connected"] = false;
}

if(isset($_GET["property_id"]) && $analytics) {
	$webproperties = $analytics->management_webproperties->listManagementWebproperties($_GET["account_id"]);		  
	foreach($webproperties as $wp) {
	  if($_GET["property_id"] == $wp["id"]) {				    				 
		  $profiles = $analytics->management_profiles->listManagementProfiles($_GET["account_id"], $_GET["property_id"]);
		  $profile = $profiles["items"][0];
		  $_SESSION["profile_id"] = $profile["id"];
	  }
  }	      		  
}
?>