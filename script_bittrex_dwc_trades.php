<?php
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'api_bittrex.php');
include($dir.'functions.php');
include($dir.'config.php');


//get webhook data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

$dataAlert = $data['alert'];
$dataAction = $data['action'];

//security measures
if($dataAlert != 'DWC') { //must have DWC for alert 
    echo 'Invalid request';
    exit;
}

/** tradingview IPs
 * 52.89.214.238
34.212.75.30
54.218.53.128
52.32.178.7
*/

$ipAddress = get_ip_address();
$recorded = date('Y-m-d h:i:s', time());
$newline = '<br />';   //debugging newline


//connect to Bittrex
$bittrex = new Client ($bittrex_api_key, $bittrex_api_secret);

//get ticker
$pair = 'USDT-LINK'; 
$percentBalance = 100; //% of your balance for buying
$getTicker = $bittrex->getTicker ($pair);

$bid = $getTicker->Bid;
$ask = $getTicker->Ask;
$fee = 0.004;

$sellQT = 0; //default quantity if you don't have the coin
$getBalances = $bittrex->getBalances();

foreach($getBalances as $index) { //go through each coin you have

    $coin = explode('-', $pair); //get coin from USDT pair

    if($index->Currency == $coin[1]) { //match coin symbol
        $sellQT = $index->Available; 
    }

    if($index->Currency == 'USDT') {
        $USDTBalance = $index->Available; 
        $buyQT = $USDTBalance/$ask; //quantity to buy
        $buyQT = $buyQT - $buyQT * $fee; //subtract taker or maker fee
    }

}

if($data['action'] == 'buy') { //set ther orders based on action
    $buyLimit = $bittrex->buyLimit($pair, $buyQT, $ask);   
    $output .= ' buy ';
}
else if($data['action'] == 'sell') {
    $sellLimit = $bittrex->sellLimit ($pair, $sellQT, $bid);
    $output .= ' sell ';
}



$output = $recorded.' | IP: '.$ipAddress.' | post data: '.$data['alert'].' | action: '.$dataAction.' | '.$data['ticker'].' | '.$newline;

$output .= 'bid: '.$bid.' | ask: '.$bid.' | buyQT: '.$buyQT.' sellQT: '.$sellQT.' '.$newline; 
echo $output;

$properties = get_object_vars($getBalances);
print_r($properties);

//$output1 = var_dump($getBalances);

if($dataAction) { 
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