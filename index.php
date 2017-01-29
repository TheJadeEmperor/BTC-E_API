<?php
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'api_poloniex.php');
include($dir.'config.php');
include($dir.'ez_sql_core.php');
include($dir.'ez_sql_mysql.php');


error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);


$create_call = 'include/ajax.php?action=create';
$read_call = 'include/ajax.php?action=read';
$update_call = 'include/ajax.php?action=update';
$delete_call = 'include/ajax.php?action=delete';


global $db;

$db = new ezSQL_mysql($dbUser, $dbPW, $dbName, $dbHost);


//requires the extension php_openssl to work
$polo = new poloniex();

//get currency prices
$BTC_ETH = $polo->get_ticker('BTC_ETH');

$USDT_BTC = $polo->get_ticker('USDT_BTC');

$USDT_ETH = $polo->get_ticker('USDT_ETH');



$tableData = new Database($db);

$condTable = $tableData->alertsTable();



include('index.html');
?>