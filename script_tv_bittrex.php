<?php
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'api_poloniex.php');
include($dir.'config.php');
include($dir.'ez_sql_core.php');
include($dir.'ez_sql_mysql.php'); 

//requires the extension php_openssl to work
$polo = new poloniex($polo_api_key, $polo_api_secret);

//database connection
$db = new ezSQL_mysql($dbUser, $dbPW, $dbName, $dbHost);

//connect to the BTC Database
$newDB = new Database($db);

$debug = 1;


//gmail API here
//if criteria is met
//From: noreply@tradingview.com 
//Subject: TradingView Alert: Short Signal 
//Subject: TradingView Alert: Long Signal 

$currencyPair = 'BTC_XRP'; 
$amount = '10';
$criteria_is_met = true;
$short_signal = true;
$long_signal = false;
$debug = 0;


if($criteria_is_met) {

	//get current price of pair
	$currentRate = $polo->get_ticker($currencyPair);

	$rate = $currentRate['last'];
	$output .= 'currencyPair: '. $currencyPair.' | amount: '.$amount.' | rate: '.$rate.' ';

	//check for open pos - bittrex
	
	
	//future: break order into smaller parts
	if($short_signal) { //open margin pos - short
		if($debug == 0) {
	
		var_dump($shortPos); 
			
			//bittrex - sell pos - if open
		}
	}
	else if ($long_signal) { //open margin pos - long
		if($debug == 0) {

			var_dump($longPos);
			
			//bittrex buy pos
		}
	}
	
	
	//send text message
	$sendMailBody = 'Opened Pos on '.$currencyPair.' | Amount: '.$amount.'';
	$output .= $sendMailBody;		
	
	if($debug == 0) {
		$newDB->sendMail($sendEmailBody); 
	} 

	echo '<br />'.$output;
}


?>