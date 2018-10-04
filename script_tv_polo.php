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
$short_signal = false;
$long_signal = false;
$debug = 0;


if($criteria_is_met) {

	//get current price of pair
	$currentRate = $polo->get_ticker($currencyPair);

	$rate = $currentRate['last'];
	$output .= 'currencyPair: '. $currencyPair.' | amount: '.$amount.' | rate: '.$rate.' ';

	//check for open pos - poloniex
	$getMarginPos = $polo->get_margin_position($currencyPair);
	echo 'getMarginPos ';
	var_dump($getMarginPos); 
	
	if($getMarginPos['type'] == 'none') //no positions open
		$adjustedAmount = $amount;
	else {
		$adjustedAmount = $amount * 2;
	}
	
	
	//check for open margin order & replace it
	if($debug == 0) {
		$openOrders = $polo->return_open_orders($currencyPair);
			 
		echo 'openOrders '; 
		var_dump($openOrders);
		if($openOrders[0]['margin'] == 1) 
			$orderNumber = $openOrders[0]['orderNumber'];
		
		if(isset($orderNumber)) {
			$moveOrder = $polo->move_order($orderNumber, $rate);
			echo 'moveOrder ';
			var_dump($moveOrder);
		}
	}
	
	if($short_signal) { //open margin pos - short
		if($debug == 0) {
			$shortPos = $polo->margin_sell($currencyPair, $rate, $adjustedAmount, 1);
			echo $typePos = 'shortPos'; 
			var_dump($shortPos); 
		}
	}
	else if ($long_signal) { //open margin pos - long
		if($debug == 0) {
			$longPos = $polo->margin_buy($currencyPair, $rate, $adjustedAmount, 1);
			echo $typePos = 'longPos';
			var_dump($longPos);
		}
	}
	
	
	//send text message
	$sendMailBody = 'Opened '.$typePos.' on Poloniex for '.$currencyPair.' | Amount: '.$amount.'';
	$output .= $sendMailBody;		
	
	if($debug == 0) {
		$newDB->sendMail($sendEmailBody); 
	} 

	echo '<br />'.$output;
}


?>