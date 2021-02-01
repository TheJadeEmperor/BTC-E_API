<?php
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'api_bitmex.php');
include($dir.'functions.php');
include($dir.'config.php');

//available symbols XBTUSD ADAH21 DOTUSDTH21 EOSH21 ETHUSD LINKUSDT LTCUSD XRPUSD YFIUSDH21
$symbol = 'EOSH21';

//connect to Bittrex
$bitmex = new Bitmex ($bitmex_api_id, $bitmex_api_secret);

$response = $bitmex->getTicker($symbol);

//$properties = get_object_vars($getBalances);
print_r($response);

$response = $bitmex->getMargin(); //empty
print_r($response);

$response = $bitmex->getOrderBook(); //empty
print_r($response);

?>