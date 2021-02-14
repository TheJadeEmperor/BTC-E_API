<?php
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'api_gate.php');
include($dir.'functions.php');
include($dir.'config.php');

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

$coin = explode('-', $pair); //USDT-GT
$pair = $coin[1].'_'.$coin[0]; //GT_USDT
echo $pair.' ';

$getMarketPrice = getMarketPrice($pair);
$bid = $getMarketPrice[0]['highest_bid'];
$ask = $getMarketPrice[0]['lowest_ask'];

$fee = 0.001; //get fee from api

$sellQT = $buyQT = 700; //set default quantity - unable to get balance from api


if($live == 1)
    if($data['action'] == 'buy') { //set the orders based on action
        $buyOrder = buyOrder('limit', $pair, $buyQT, $ask);
        $orderId = $buyOrder['id'];
    }
    else if($data['action'] == 'sell') {
        $sellOrder =  sellOrder('limit', $pair, $sellQT, $bid);
        $orderId = $sellOrder['id'];
    }

$output = 'live: '.$live.' | '.$recorded.' | IP: '.$ipAddress.' | post data: '.$data['alert'].' | action: '.$dataAction.' | '.$data['ticker'].' | '.$newline;

$output .= 'bid: '.$bid.' | ask: '.$bid.' | buyQT: '.$buyQT.' sellQT: '.$sellQT.' | totalBalance: '.$totalBalance.$newline; 
echo $output;


//$output1 = var_dump($getBalances);

if($dataAction && $live == 0) { 

    //write to log db
    $insert = 'INSERT INTO '.$logTableName.' (recorded, log) values ("'.$recorded.'", "'.$output.'")';
    $res = $conn->query($insert);
}
   

?>