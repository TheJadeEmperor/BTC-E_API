<?php
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'api_bittrex.php');
include($dir.'functions.php');
include($dir.'config.php');


//connect to Bittrex
$bittrex = new Client ($bittrex_api_key, $bittrex_api_secret);

$type = 'buy';
$market = 'USDT-ADA';
$balance = 20;

//get orderbook
$getOrderBook = $bittrex->getOrderBook ($market, $type, 10);

//var_dump($getOrderBook);
//$buyLimit = $bittrex->buyLimit ($market, '21', '0.866');

var_dump($buyLimit);
$orderId = $buyLimit->uuid; 

//$buyMarket = $bittrex->buyMarket ($market, '10');
//var_dump($buyMarket);

foreach($getOrderBook as $orderBook) {

    //var_dump($orderBook);
    $sellQT = $orderBook->Quantity;
    $bid = $orderBook->Rate;
    $cost = $sellQT * $bid; 

    //$sellLimit = $bittrex->sellLimit ($pair, $sellQT, $bid);
    $balance = $balance - $cost; 

    $orderId = $sellLimit->uuid; 
echo    $output = 'bid: '.$bid.' | sellQT: '.$sellQT.' | cost: '.$cost.' | balance: '.$balance.' | orderId: '.$orderId.'<br />';

    if(!$orderId) 
        break;

}


?>