<?php

include('include/api_database.php');
include('include/api_poloniex.php');
include('include/config.php');


error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

//requires the extension php_openssl to work
$polo = new poloniex();


$BTC_ETH = $polo->get_ticker('BTC_ETH');

$USDT_BTC = $polo->get_ticker('USDT_BTC');

$USDT_ETH = $polo->get_ticker('USDT_ETH');

//$candleData = new Database($db);
//$candleData->sendMail();

?>




<?
//exit;

include('index.html');
?>