<?php
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'api_bittrex.php');
include($dir.'config.php');

//debug mode or live mode
$server = $_SERVER['SERVER_NAME'];
if ($server == 'localhost' || $server == 'btcAPI.test') {
	$newline = '<br />';   //debugging newline
	$cronjob = 0;
	
	if($_GET['cronjob'] == 1 || $_GET['live'] == 1) 
		$cronjob = 1; //live mode in localhost
}
else { //cron job - newline is \n
	$newline = "\n";
	$cronjob = 1;
}

//connect to Bittrex
$bittrex = new Client ($bittrex_api_key, $bittrex_api_secret);

/** tradingview IPs
 * 52.89.214.238
34.212.75.30
54.218.53.128
52.32.178.7

{"alert":"DWC","action":"{{strategy.order.action}}","ticker":"{{ticker}}"}

*/
//get webhook data
$json = file_get_contents('php://input');
$action = json_decode($json, true);

echo $action; 

//write to file
$myFile = "log.txt";
$fh = fopen($myFile, 'a') or print("Can't open file $myFile");
fwrite($fh, $action); 
 

//get ticker
$pair = 'USDT-LTC';
$getTicker = $bittrex->getTicker ($pair);

$bid = $getTicker->Bid;
$ask = $getTicker->Ask;
$fee = 0.004;

$getBalances =  $bittrex->getBalances();

foreach($getBalances as $index) { //go through each coin you have
//    if($cronjob == 0) {
  //      echo ($index->Currency).' '; }

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

//buyLimit ($market, $quantity, $rate)
//$buyLimit = $bittrex->buyLimit($pair, $buyQT, $ask);   
 

//$sellLimit = $bittrex->sellLimit ($pair, $sellQT, $bid);
    
if($cronjob == 0) {
    echo $newline. 'bid: '.$bid.' | ask: '.$bid.' | buyQT: '.$buyQT.' sellQT: '.$sellQT;
    echo '<pre>';print_r($getBalances);
    echo '</pre>';
}
else {
    fwrite($fh, $action); 
    fclose($fh);    
}
   

?>