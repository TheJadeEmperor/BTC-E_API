<?php
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'api_kucoin.php');
include($dir.'functions.php');
include($dir.'config.php');

//kucoin subaccount keys
$sub = $_GET['sub'];
if ($sub == 'kucoin5') {
    $key = $kucoin5_key;
    $secret = $kucoin5_secret;
    $passphrase = $kucoin5_passphrase;
} 

$ipAddress = get_ip_address(); 
$recorded = date('Y-m-d H:i:s', time());
$newline = '<br />';   //debugging newline
$database = new Database($conn);

//get webhook data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

$dataAlert = $data['alert'];
$dataAction = $data['action'];
$pair = $data['ticker'];

//IP white list from tradingview
$trustedIPs = array(
    '52.89.214.238',
    '34.212.75.30',
    '54.218.53.128',
    '52.32.178.7',
);

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
$coin = explode('-', $pair); //USDT-XRP
$pair = $coin[1].'-'.$coin[0]; //XRP-USDT

$percentBalance = 1; //% of your balance for purchases | 1=100% | 0.5=50%

$getPrices = getMarketPrice($pair);
$ask = $getPrices['data']['bestAsk'];
$bid = $getPrices['data']['bestBid'];

$sellQT = $buyQT = 0; //default quantity if you don't have the coin
$getBalances = checkBalance();
$totalBalance = 0;

foreach($getBalances['data'] as $index) { //go through each coin you have
   $available = $index['available'];
   
   if($index['currency'] == $coin[1] && $available > 0) { //match coin symbol   
        $coinBalance = $available; 
        if($index['available'] > 0) { //check for available balance
            $sellQT = $index['available']; 
            $totalBalance += $sellQT * $bid;
        }
    }

    if($index['currency'] == 'USDT' && $available > 0) {
        $USDTBalance = $available; 
        $totalBalance += $USDTBalance; //add to totalBalance
        $buyQT = $USDTBalance/$ask; //quantity to buy
        $buyQT = $buyQT * $percentBalance; 
    }
}

//orders only take 4 decimals
$buyQT = number_format($buyQT, 4, '.', '');
$sellQT = number_format($sellQT, 4, '.', '');
$coinBalance = number_format($coinBalance, 4, '.', '');
$USDTBalance = number_format($USDTBalance, 4, '.', '');
$totalBalance = number_format($totalBalance, 4, '.', '');

//fix balance insufficient error
if($sellQT > $index['available']) //balance is rounded up from number_format
   $sellQT = $sellQT - 0.0001; //balance needs to be rounded down

if($live == 1)
    if($data['action'] == 'buy') { //set the orders based on action
        //pair examples: XRP-USDT BTC-USDT
        $buyResult = buyOrder('market', $pair, $buyQT, $ask);
        $orderId = $buyResult['data']['orderId'];
    }
    else if($data['action'] == 'sell') {
        $sellResult = sellOrder('market', $pair, $sellQT, $bid);
        $orderId = $sellResult['data']['orderId'];
    }

include('include/logInsert.php');

?>