<?php
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'api_poloniex.php');
include($dir.'api_btce.php');
include($dir.'config.php');
include($dir.'ez_sql_core.php');
include($dir.'ez_sql_mysql.php');

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

//set timezone
date_default_timezone_set('America/New_York');


//alert ajax calls
$createAlert = 'include/ajax.php?action=create';
$readAlert = 'include/ajax.php?action=read';
$updateAlert = 'include/ajax.php?action=update';
$deleteAlert = 'include/ajax.php?action=delete';

//trade Ajax calls
$createTrade = 'include/ajax.php?action=createTrade';
$readTrade = 'include/ajax.php?action=readTrade';
$updateTrade = 'include/ajax.php?action=updateTrade';
$deleteTrade = 'include/ajax.php?action=deleteTrade';

global $db;

$db = new ezSQL_mysql($dbUser, $dbPW, $dbName, $dbHost);


//requires the extension php_openssl to work
$polo = new poloniex();

$btce = new BTCeAPI();

$tableData = new Database($db);

$condTable = $tableData->alertsTable();

$tradesTable = $tableData->tradesTable();



//get prices from poloniex
$BTC_ETH = $polo->get_ticker('BTC_ETH');

$USDT_BTC = $polo->get_ticker('USDT_BTC');

$USDT_ETH = $polo->get_ticker('USDT_ETH');


//get prices from btc-e

$btce_btc_eth = $btce->getLastPrice('eth_btc');

$btce_btc_usd = $btce->getLastPrice('btc_usd');

$btce_eth_usd = $btce->getLastPrice('eth_usd');


//format polo currencies
$polo_btc_usd = number_format($USDT_BTC['last'], 0);

$polo_eth_usd= number_format($USDT_ETH['last'], 2);

$polo_btc_eth = number_format($BTC_ETH['last'], 4);


//format btc-e currencies
$btce_btc_usd = number_format($btce_btc_usd, 0);

$btce_eth_usd = number_format($btce_eth_usd, 2);

$btce_btc_eth =  number_format($btce_btc_eth, 4);


//currency options
$currTypes = array(
	'BTC/USDT',
	'ETH/USDT',
	'ETH/BTC',
	'XRP/BTC'
);

foreach($currTypes as $cType) {
	$alertCurrencyDropDown .= '<option value="'.$cType.'">'.$cType.'</option>';
	$tradeCurrencyDropDown .= '<option value="'.$cType.'">'.$cType.'</option>';
}
$currencyDropDown = '<select name="currency">'.$alertCurrencyDropDown.'</option>';
$tradeCurrencyDropDown = '<select name="trade_currency">'.$tradeCurrencyDropDown.'</option>';



//condition types
$conditionTypes = array(
	'<=',
	'>=',
);

foreach($conditionTypes as $condType) {
	$conditionDropDown .= '<option value="'.$condType.'">'.$condType.'</option>';
	$tradeConditionDropDown .= '<option value="'.$condType.'">'.$condType.'</option>';
}
$conditionDropDown = '<select name="on_condition">'.$conditionDropDown.'</option>';

$tradeConditionDropDown = '<select name="trade_condition">'.$tradeConditionDropDown.'</select>';



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
	$tradeExchangeDropDown .= '<option value="'.$eType.'">'.$eType.'</option>';
}
$exchangeDropDown = '<select name="exchange">'.$exchangeDropDown.'</option>';
$tradeExchangeDropDown = '<select name="trade_exchange">'.$tradeExchangeDropDown.'</option>';
 


$sentTypes = array(
	'No', 'Yes',
);

foreach($sentTypes as $sType) {
	$sentDropDown .= '<option value="'.$sType.'">'.$sType.'</option>';
}
$sentDropDown = '<select name="sent">'.$sentDropDown.'</option>';
 

$actionTypes = array(
	'Buy', 'Sell'
); 

foreach($actionTypes as $aType) {
	$actionDropDown .= '<option value="'.$aType.'">'.$aType.'</option>'; 
}
$tradeActionDropDown = '<select name="trade_action">'.$actionDropDown.'</option>';


include('index.html');
?>