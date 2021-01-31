<?php
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'api_bittrex.php');
include($dir.'config.php');

$newline = '<br />';   //debugging newline

/** tradingview IPs
 * 52.89.214.238
34.212.75.30
54.218.53.128
52.32.178.7
*/
//get webhook data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

//security measures
if($data['alert'] != 'DWC') {
    echo 'Invalid request';
    exit;
}


//connect to Bittrex
$bittrex = new Client ($bittrex_api_key, $bittrex_api_secret);

//get ticker
$pair = 'USDT-LINK'; 
$percentBalance = 100; //% of your balance for buying
$getTicker = $bittrex->getTicker ($pair);

$bid = $getTicker->Bid;
$ask = $getTicker->Ask;
$fee = 0.004;

$getBalances =  $bittrex->getBalances();

foreach($getBalances as $index) { //go through each coin you have

    $coin = explode('-', $pair); //get coin from USDT pair

    if($index->Currency == $coin[1]) { //match coin symbol
        echo 'true';  $sellQT = $index->Available; 
    }

    if($index->Currency == 'USDT') {
        $USDTBalance = $index->Available; 
        $buyQT = $USDTBalance/$ask; //quantity to buy
        $buyQT = $buyQT - $buyQT * 0.004; //subtract taker or maker fee
    }

}

if($data['action'] == 'buy') {
    //buyLimit ($market, $quantity, $rate)
    $buyLimit = $bittrex->buyLimit($pair, $buyQT, $ask);   
    $output .= 'buy';
}
else if($data['action'] == 'sell') {
    $sellLimit = $bittrex->sellLimit ($pair, $sellQT, $bid);
    $output .= 'sell';
}

$output = 'cronjob: '.$cronjob.' | post data: '.$data['alert'].' | '.$data['action'].' '.$data['ticker'].' | '.$newline;

$output .= $newline. 'bid: '.$bid.' | ask: '.$bid.' | buyQT: '.$buyQT.' sellQT: '.$sellQT; 
echo $output;


$output1 = '<pre>';print_r($getBalances).'</pre>';

echo     $date = date(time(), 'Y-m-d h:i:s');


if($cronjob == 1) {
    //write to file
    $myFile = "log.txt";
    $fh = fopen($myFile, 'a') or print("Can't open file $myFile");
    fwrite($fh, $output); 
    fclose($fh);    

    $date = date(time(), 'Y-m-d h:i:s');
    //write to log db
    $insert = 'INSERT INTO $logTableName (recorded, log) values ("'.$date.'", "'.$output.'")';
    $res = $db->query($insert);
}
   

?>