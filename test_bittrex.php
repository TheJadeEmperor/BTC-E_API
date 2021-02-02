<?php
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'api_bittrex.php');
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
    '52.32.178.7'
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

//connect to Bittrex
$bittrex = new Client ($bittrex_api_key, $bittrex_api_secret);

// /$pair = 'USDT-LINK'; 
$percentBalance = 1; //% of your balance for purchases | 1=100% | 0.5=50%
$getTicker = $bittrex->getTicker ($pair);

$bid = $getTicker->Bid; //for sells
$ask = $getTicker->Ask; //for buys
$fee = 0.004; //get fee from api

$sellQT = $buyQT = 0; //default quantity if you don't have the coin
$getBalances = $bittrex->getBalances();
$totalBalance = 0;

foreach($getBalances as $index) { //go through each coin you have

    $coin = explode('-', $pair); //get coin from USDT pair

    if($index->Currency == $coin[1]) { //match coin symbol
        $sellQT = $index->Available; 
        $sellQT = $sellQT * $percentBalance;
        $totalBalance += $sellQT * $bid;
    }

    if($index->Currency == 'USDT') {
        $USDTBalance = $index->Available; 
        $totalBalance += $USDBalance; //add to totalBalance
        $buyQT = $USDTBalance/$ask; //quantity to buy
        $buyQT = $buyQT - $buyQT * $fee; //subtract taker or maker fee
        $buyQT = $buyQT * $percentBalance; 
    }
}

if($live == 1)
    if($data['action'] == 'buy') { //set the orders based on action
        //pair examples: USDT-LINK BTC-LINK
        $buyLimit = $bittrex->buyLimit($pair, $buyQT, $ask);   
        //$output .= ' buy ';
    }
    else if($data['action'] == 'sell') {
        $sellLimit = $bittrex->sellLimit ($pair, $sellQT, $bid);
        //$output .= ' sell ';
    }



$output = 'live: '.$live.' | '.$recorded.' | IP: '.$ipAddress.' | post data: '.$data['alert'].' | action: '.$dataAction.' | '.$data['ticker'].' | '.$newline;

$output .= 'bid: '.$bid.' | ask: '.$bid.' | buyQT: '.$buyQT.' sellQT: '.$sellQT.' | totalBalance: '.$totalBalance.$newline; 
echo $output;

$properties = get_object_vars($getBalances);
print_r($properties);

//$output1 = var_dump($getBalances);

if($dataAction && $live == 0) { 
    //write to file
    $myFile = "log.txt";
    $fh = fopen($myFile, 'a') or print("Can't open file $myFile");
    fwrite($fh, $output); 
    fclose($fh);    

    //write to log db
    $insert = 'INSERT INTO '.$logTableName.' (recorded, log) values ("'.$recorded.'", "'.$output.'")';
    $res = $conn->query($insert);
}
   

?>