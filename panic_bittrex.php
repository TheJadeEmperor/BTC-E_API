<?php
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'api_bittrex.php');
include($dir.'functions.php');
include($dir.'config.php');

$ipAddress = get_ip_address(); 
$recorded = date('Y-m-d H:i:s', time());
$newline = '<br />';   //debugging newline
$database = new Database($conn);
$sub = 'bittrex';

//get webhook data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

$dataAlert = $data['alert'];
$dataAction = $data['action'];
$pair = $data['ticker'];
$amt = $data['amt'];

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
//connect to Bittrex
$bittrex = new Client ($bittrex_api_key, $bittrex_api_secret);

$market = 'USDT-ADA';
$balance = 5; //do not go over this amt in USDT

//$action = 'buy';
//$action = 'sell';

$getOpenOrders = $bittrex->getOpenOrders($market);

echo 'getOpenOrders ';
var_dump($getOpenOrders);

foreach($getOpenOrders as $orderDetails) {
    echo ' '.$orderDetails->OrderUuid.' ';
    echo $bittrex->cancel ($orderDetails->OrderUuid);
}

sleep(2);


if ($live == 1)
    $output = $bittrex->useOrderBook ($market, $balance, $dataAction);

echo $output;


?>