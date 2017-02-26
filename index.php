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


//get prices from poloniex
$BTC_ETH = $polo->get_ticker('BTC_ETH');

$USDT_BTC = $polo->get_ticker('USDT_BTC');

$USDT_ETH = $polo->get_ticker('USDT_ETH');


//get prices from btc-e
$btce_btc_usd = $btce->getLastPrice('btc_usd');

$btce_btc_eth = $btce->getLastPrice('eth_btc');

$btce_btc_usd = $btce->getLastPrice('btc_usd');

$btce_eth_usd = $btce->getLastPrice('eth_usd');


//format currencies
$btce_btc_eth = number_format($btce_btc_eth, 4);

$btce_btc_usd = number_format($btce_btc_usd, 2);

$btce_eth_usd = number_format($btce_eth_usd, 2);



$currTypes = array(
	'Any',
	'BTC/USDT',
	'ETH/USDT',
	'ETH/BTC'
);

foreach($currTypes as $cType) {
	$currencyDropDown .= '<option value="'.$cType.'">'.$cType.'</option>';
}
$currencyDropDown = '<select name="currency">'.$currencyDropDown.'</option>';



$conditionTypes = array(
	'<=',
	'>=',
);

foreach($conditionTypes as $condType) {
	$conditionDropDown .= '<option value="'.$condType.'">'.$condType.'</option>';
}
$conditionDropDown = '<select name="on_condition">'.$conditionDropDown.'</option>';



$unitTypes = array(
	'$',
	'%',
);

foreach($unitTypes as $uType) {
	$unitDropDown .= '<option value="'.$uType.'">'.$uType.'</option>';
}
$unitDropDown = '<select name="unit">'.$unitDropDown.'</option>';




$exchangeTypes = array(
	'Poloniex'
);

foreach($exchangeTypes as $eType) {
	$exchangeDropDown .= '<option value="'.$eType.'">'.$eType.'</option>';
}
$exchangeDropDown = '<select name="exchange">'.$exchangeDropDown.'</option>';




$sentTypes = array(
	'No',
	'Yes',
);

foreach($sentTypes as $sType) {
	$sentDropDown .= '<option value="'.$sType.'">'.$sType.'</option>';
}
$sentDropDown = '<select name="sent">'.$sentDropDown.'</option>';
 


include('index.html');
?>