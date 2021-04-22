<?php
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'functions.php');
include($dir.'config.php');
include($dir.'api_binance.php');

$ipAddress = get_ip_address(); 
$recorded = date('Y-m-d H:i:s', time());
$newline = '<br />';   //debugging newline
$database = new Database($conn);
$sub = 'binance';

//get webhook data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

$dataAlert = $data['alert'];
$dataAction = $data['action'];
$pair = $data['ticker'];
$amt = $data['amt'];

//security measures
if($dataAlert != 'DWC') { //must have DWC for alert 
    echo 'Invalid request';
    exit;
}
else if(!in_array($ipAddress, $trustedIPs)) {
    $live = 0; //live = 0 means test mode
}
else {
    $live = 1;
}
//////////////////////////////
$live = 1; //delete when live
//////////////////////////////
$coin = explode('-', $pair); //USDT-VTHO
$pair = $coin[1].''.$coin[0]; //VTHOUSDT

$binance = new Binance($binance_api_key, $binance_api_secret);
$getAccount = $binance->getAccount();


$getMarketPrice = $binance->getMarketPrice($pair);
// print_r($getMarketPrice);

$bid = $getMarketPrice['bids'][0][0];
$ask = $getMarketPrice['asks'][0][0];

$getAccount = $getAccount['balances'];
foreach($getAccount as $index) {
    $currency = $index['asset'];
    $available = $index['free'];

    if($available > 0) { //check for available balance
        if($currency == $coin[1]) { //match coin symbol   
            $coinBalance = $available; 
         
            $sellQT = $available; 
            $totalBalance += $sellQT * $bid;
        }
        else if($currency == 'USDT') {
            $USDTBalance = $available; 
            $totalBalance += $USDTBalance; //add to totalBalance
            $buyQT = $USDTBalance/$ask; //quantity to buy
            echo $available.' '.$currency.' <br />';
    
        }
    }
}

//subtract fee from total amt
$fee = 0.005; //0.5% 
$sellQT = $sellQT - ($sellQT * $fee); 
$buyQT = floor($buyQT);

//Fix error: precision is over the maximum defined for this asset
$bid = number_format($bid, 4, '.', '');
$ask = number_format($ask, 4, '.', '');
$buyQT = number_format($buyQT, 1, '.', ''); 
$sellQT = number_format($sellQT, 1, '.', '');


if($amt) { //override amt from json data
    $buyQT = $sellQT = $amt;
}

if($live == 1)
    if ($dataAction == 'buy') { 
        //buy order - market
        //$orderId = buyOrder('MARKET', $pair, $buyQT, $ask);
        $orderId = $binance->buyOrder('MARKET', $pair, $buyQT, $ask);
    }
    else if ($dataAction == 'sell') {
        //echo 'amt: '.$sellQT;
        // $orderId = sellOrder('MARKET', $pair, $sellQT, $bid);
        $orderId = $binance->sellOrder('LIMIT', $pair, $sellQT, $bid);
    }


include('include/logInsert.php'); 

?>