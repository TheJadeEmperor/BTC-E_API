<?php
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'api_bittrex.php');
include($dir.'functions.php');
include($dir.'config.php');

//////////////
// $live = 1;
//////////////
//connect to Bittrex
$bittrex = new Client ($bittrex_api_key, $bittrex_api_secret);

$market = 'USDT-ADA';
$balance = 5; //do not go over this amt in USDT
//test buy & sell orders from order book
$action = 'buy';
$action = 'sell';

$getOpenOrders = $bittrex->getOpenOrders($market);

echo 'getOpenOrders ';
var_dump($getOpenOrders);

foreach($getOpenOrders as $orderDetails) {
    echo ' '.$orderDetails->OrderUuid.' ';
    echo $bittrex->cancel ($orderDetails->OrderUuid);
}

sleep(2);

if ($action == 'buy')
    $orderBookType = 'sell';
else
    $orderBookType = 'buy'; 

if ($live == 1)
    $output = $bittrex->useOrderBook ($market, $balance, $orderBookType);

echo $output;


?>