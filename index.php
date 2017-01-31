<?php
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'api_poloniex.php');
include($dir.'api_btce.php');
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

$btce = new BTCeAPI();

$tableData = new Database($db);

$condTable = $tableData->alertsTable();


//get currency prices
$BTC_ETH = $polo->get_ticker('BTC_ETH');

$USDT_BTC = $polo->get_ticker('USDT_BTC');

$USDT_ETH = $polo->get_ticker('USDT_ETH');

$btce_btc_usd = $btce->getLastPrice('btc_usd');

$btce_btc_eth = $btce->getLastPrice('eth_btc');

$btce_eth_usd = $btce->getLastPrice('eth_usd');




include('index.html');
?>