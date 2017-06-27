<?php
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'api_poloniex.php');
include($dir.'config.php');
include($dir.'ez_sql_core.php');
include($dir.'ez_sql_mysql.php');

//set timezone
date_default_timezone_set('America/New_York');

//get timestamp
$currentTime = date('Y-m-d H:i:s', time());

//database connection
$db = new ezSQL_mysql($dbUser, $dbPW, $dbName, $dbHost);


$debug = $_GET['debug'];

//connect to the BTC Database
$tableData = new Database($db);

//connect to Poloniex
$polo = new poloniex($polo_api_key, $polo_api_secret);


$num = 33964;

$tradeAmount = number_format($num, 8, '.', '');

echo $tradeAmount;

?>