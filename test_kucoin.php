<?php
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'api_kucoin_1.php');
include($dir.'functions.php');
include($dir.'config.php');


//kucoin subaccount keys
$sub = $_GET['sub'];
if($sub == 'kucoin2') {
    $key = $kucoin2_key;
    $secret = $kucoin2_secret;
    $passphrase = $kucoin2_passphrase;
}
else if ($sub == 'kucoin3') {
    $key = $kucoin3_key;
    $secret = $kucoin3_secret;
    $passphrase = $kucoin3_passphrase;
}
else if ($sub == 'kucoin4') {
    $key = $kucoin4_key;
    $secret = $kucoin4_secret;
    $passphrase = $kucoin4_passphrase;
}
else { //default is kucoin1
    $key = $kucoin1_key;
    $secret = $kucoin1_secret;
    $passphrase = $kucoin1_passphrase;    
}


$ipAddress = get_ip_address(); 
$recorded = date('Y-m-d h:i:s', time());
$newline = '<br />';   //debugging newline

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

//$pair = 'USDT-XRP'; //delete when live
$live = 1; //delete when live

$coin = explode('-', $pair); //USDT-XRP
echo ' '.$coin[1].' '; 
$pair = $coin[1].'-'.$coin[0]; //XRP-USDT

$percentBalance = 1; //% of your balance for purchases | 1=100% | 0.5=50%

$getPrices = getMarketPrice($pair);
$ask = $getPrices['data']['bestAsk'];
$bid = $getPrices['data']['bestBid'];
$fee = 0.002; //taker or maker fee

$sellQT = $buyQT = 0; //default quantity if you don't have the coin
$getBalances = checkBalance();
$totalBalance = 0;

foreach($getBalances['data'] as $index) { //go through each coin you have
   // echo $index['currency'];
    if($index['currency'] == $coin[1]) { //match coin symbol
        //echo $coin[1]. ' ';
        if($index['available'] > 0) { //check for available balance
            $sellQT = $index['available']; 
            $totalBalance += $sellQT * $bid;
        }
    }

    if($index['currency'] == 'USDT') {
        $USDTBalance = $index['available']; 
        $totalBalance += $USDTBalance; //add to totalBalance
        $buyQT = $USDTBalance/$ask; //quantity to buy
        $buyQT = $buyQT - $buyQT * $fee; //subtract taker or maker fee
        $buyQT = $buyQT * $percentBalance; 
    }

}

//echo 'sellQT '. $sellQT;
echo $buyQT = number_format($buyQT, 4, '.', '');
echo $sellQT = number_format($sellQT, 4, '.', '');

if($live == 1)
    if($data['action'] == 'buy') { //set the orders based on action
        //pair examples: XRP-USDT BTC-USDT
        $buyResult = buyLimit($pair, $buyQT, $ask);
        $orderId = $buyResult['data']['orderId'];
    }
    else if($data['action'] == 'sell') {
        $sellResult = sellLimit($pair, $sellQT, $bid);
        $orderId = $sellResult['data']['orderId'];
    }

//echo 'bid '.$bid;

//cancelOrder($orderID);

$output = 'live: '.$live.' | '.$recorded.' | IP: '.$ipAddress.' | post data: '.$data['alert'].' | action: '.$dataAction.' | '.$data['ticker'].' | '.$newline;

$output .= 'bid: '.$bid.' | ask: '.$bid.' | buyQT: '.$buyQT.' sellQT: '.$sellQT.' | totalBalance: '.$totalBalance.' | orderId: '.$orderId.$newline; 
echo $output;


if($dataAction) { 
    //write to log db
    $insert = 'INSERT INTO '.$logTableName.' (recorded, log, exchange, action) values ("'.$recorded.'", "'.$output.'",  "kucoin1",  "'.$dataAction.'")';
    $res = $conn->query($insert);
}
   


?>