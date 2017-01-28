<?php

include('include/api_database.php');
include('include/api_poloniex.php');
include('include/config.php');

$create_call = 'include/ajax.php?action=create';
$read_call = 'include/ajax.php?action=read';
$update_call = 'include/ajax.php?action=update';
$delete_call = 'include/ajax.php?action=delete';


error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

//requires the extension php_openssl to work
$polo = new poloniex();


$BTC_ETH = $polo->get_ticker('BTC_ETH');

$USDT_BTC = $polo->get_ticker('USDT_BTC');

$USDT_ETH = $polo->get_ticker('USDT_ETH');

//$candleData = new Database($db);
//$candleData->sendMail();


//exit;

include('index.html');
?>