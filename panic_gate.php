<?php
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'api_gate.php');
include($dir.'functions.php');
include($dir.'config.php');

$ipAddress = get_ip_address(); 
$recorded = date('Y-m-d H:i:s', time());
$newline = '<br />';   //debugging newline
$database = new Database($conn);
$sub = 'gate1';

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
$Gate = new Gate($gate_key, $gate_secret);

$coin = explode('-', $pair); //USDT-GT
$pair = $coin[1].'_'.$coin[0]; //GT_USDT

$getMarketPrice = $Gate->getMarketPrice($pair);
$bid = $getMarketPrice[0]['highest_bid'];
$ask = $getMarketPrice[0]['lowest_ask'];

$getBalances = $Gate->getBalances();

foreach($getBalances as $index) {
    $currency = $index['currency'];
    $available = $index['available'];
    
    if($available > 0) { //check for available balance
        if($currency == $coin[1]) { //match coin symbol   
            $coinBalance = $available; 
            echo $available.' '.$currency.' <br />';
    
            $sellQT = $available; 
            $totalBalance += $sellQT * $bid;
        }
        else if($currency == 'USDT') {
            $USDTBalance = $available; 
            $totalBalance += $USDTBalance; //add to totalBalance
            $buyQT = $USDTBalance/$ask; //quantity to buy
            $buyQT = $buyQT * $percentBalance; 
        }
    }
} //foreach($getBalances as $index)

//orders only take 4 decimals
$buyQT = number_format($buyQT, 4, '.', '');
$sellQT = number_format($sellQT, 4, '.', '');
$coinBalance = number_format($coinBalance, 4, '.', '');
$USDTBalance = number_format($USDTBalance, 4, '.', '');
$totalBalance = number_format($totalBalance, 4, '.', '');

if($amt) { //override amt from json data
    $buyQT = $sellQT = $amt;
}

if($live == 1)
    if($data['action'] == 'buy') { //set the orders based on action
        $buyOrder = buyOrder('market', $pair, $buyQT, $ask);
        $orderId = $buyOrder['id'];
    }
    else if($data['action'] == 'sell') {

        $sellOrder = sellOrder('market', $pair, $sellQT, $bid);
        $orderId = $sellOrder['id'];
    }

include('include/logInsert.php');

?>