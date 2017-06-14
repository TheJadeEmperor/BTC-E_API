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
$createAlert = 'include/ajax.php?action=create4';
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
$polo = new poloniex($polo_api_key, $polo_api_secret);

$btce = new BTCeAPI();

$tableData = new Database($db);

$condTable = $tableData->alertsTable();

$tradesTable = $tableData->tradesTable();


//get prices from poloniex
$POLO_USDT_DASH = $polo->get_ticker('USDT_DASH');

$POLO_USDT_BTC = $polo->get_ticker('USDT_BTC');

$POLO_USDT_ETH = $polo->get_ticker('USDT_ETH');

$POLO_USDT_LTC = $polo->get_ticker('USDT_LTC');


//format polo currencies
$polo_dash_usd = number_format($POLO_USDT_DASH['last'], 2);

$polo_btc_usd = number_format($POLO_USDT_BTC['last'], 0);

$polo_eth_usd = number_format($POLO_USDT_ETH['last'], 2);

$polo_ltc_usd = number_format($POLO_USDT_LTC['last'], 2);


//get prices from btc-e
$btce_dash_usd = $btce->getLastPrice('dsh_usd');

$btce_btc_usd = $btce->getLastPrice('btc_usd');

$btce_eth_usd = $btce->getLastPrice('eth_usd');

$btce_ltc_usd = $btce->getLastPrice('ltc_usd');

//format btc-e currencies
$btce_dash_usd =  number_format($btce_dash_usd, 2);

$btce_btc_usd = number_format($btce_btc_usd, 0);

$btce_eth_usd = number_format($btce_eth_usd, 2);

$btce_ltc_usd =  number_format($btce_ltc_usd, 2);



$conditionDropDown = '<select name="on_condition"><option value=">"> > </option><option value="<"> < </option></select>';

$tradeConditionDropDown = '<select name="trade_condition"><option value=">"> > </option><option value="<"> < </option></select>';



$unitTypes = array(
	'BTC',
	'%',
);

foreach($unitTypes as $uType) {
	$unitDropDown .= '<option value="'.$uType.'">'.$uType.'</option>';
}
$tradeUnitDropDown = '<select name="trade_unit">'.$unitDropDown.'</option>';



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