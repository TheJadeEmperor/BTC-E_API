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

$percentBalance = 1; //% of your balance for purchases | 1=100% | 0.5=50%
$getTicker = $bittrex->getTicker ($pair);

$sellQT = $buyQT = 0; //default quantity if you don't have the coin
$getBalances = $bittrex->getBalances();
$totalBalance = 0;

$properties = get_object_vars($getBalances);
var_dump($properties);

if($amt) { //override the amt
    $buyQT = $sellQT = $amt;
}

if ($live == 1) 
    $output = $bittrex->useOrderBook ($pair, $amt, $dataAction);

echo $output;


?>