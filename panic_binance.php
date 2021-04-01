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

$pair = 'VTHOUSDT';
$getAccount = getAccount(); 
$getAccount = $getAccount['balances'];
foreach($getAccount as $num => $coin) {
    if ($coin['asset'] == 'USDT') {
        echo 'usdt '.$coin['free'];
        $usdtValue = $coin['free'];
    }
}

$getMarketPrice = getMarketPrice($pair);

$bid = $getMarketPrice['bids'][0][0];
$ask = $getMarketPrice['asks'][0][0];

$buyQT = $usdtValue / $ask;

if ($dataAction == 'buy') {
    //buy order - market
    $orderId = buyOrder('MARKET', $pair, $buyQT, $ask);
} 
else if ($dataAction == 'sell') {
    //subtract fee from total amt
    $fee = 0.001; //0.1%
    $sellQT = $amt - ($amt * $fee);
    
//echo 'amt: '.$sellQT;
    //sell order - market
    $orderId = sellOrder('MARKET', $pair, $sellQT, $bid);
}


include('include/logInsert.php'); 


?>