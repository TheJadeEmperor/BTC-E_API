<?php
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'api_bittrex.php');
include($dir.'functions.php');
include($dir.'config.php');

//$live = 0;

//connect to Bittrex
$bittrex = new Client ($bittrex_api_key, $bittrex_api_secret);

//test buy & sell orders from order book
$action = 'buy';
//$action = 'sell';

$market = 'USDT-ADA';
$balance = 10; //do not go over this amt in USDT

//get orderbook getOrderBook ($market, $type, $depth)
$getOrderBook = $bittrex->getOrderBook ($market, $action, 10);
 
foreach($getOrderBook as $orderBook) {
    $bid = $orderBook->Rate;
    var_dump($orderBook);

    $QT = $balance/$bid;

    if ($orderBook->Quantity <= $QT)
        $QT = $orderBook->Quantity;

    $cost = $QT * $bid; 

    if($balance > 0) {
        if ($live == 1)
            if ($action == 'buy')
                $limitOrder = $bittrex->buyLimit ($market, $QT, $bid);
            else if($action == 'sell')
                $limitOrder = $bittrex->sellLimit ($market, $QT, $bid);

        var_dump($limitOrder);

        $orderId = $limitOrder->uuid; 

        $balance = $balance - $cost; 

        $output .= 'action: '. $action.' |  bid: '.$bid.' | QT: '.$QT.' | cost: '.$cost.' | balance: '.$balance.' | orderId: '.$orderId.'<br />';
    }
    else {
        $output .= 'end loop';
        break;
    }
}

echo $output;


?>