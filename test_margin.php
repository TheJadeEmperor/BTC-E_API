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



echo $currencyPair = 'BTC_XRP';
$amt = '250';



//$rrr = $polo->close_margin_position($currencyPair, $order_number);
//print_r($rrr);


//$rrr = $polo->margin_sell($currencyPair, $rate, $amt, 1);
//print_r($rrr);


//gmail API here
//if criteria is met
//From: noreply@tradingview.com 
//Subject: TradingView Alert: Short Signal 

if($criteria_is_met) {
			
	$currencyPair = 'BTC_ETH'; 
	$amount = '0.01';

	//get current price of pair

	
	$rate = 'current rate';  
	$output .= $currencyPair.' '.$amount.' '.$rate.' ';		
	
	
	//check for open pos & close it
	if($debug == 0) {
		$closePos = $polo->close_margin_position($currencyPair, $order_number);
			
		print_r($closePos); 
		
	}
		

	//after upgrading tradingview
	//	$shortPos = $polo->margin_sell($currencyPair, $rate, $amt, 1);
	

	if($debug == 0) {
		//open margin pos - long
		$longPos = $polo->margin_buy($currencyPair, $rate, $amt, 1);
		print_r($longPos);
		
		//future - bittrex go long
	}

	//send text message
	$sendMailBody = 'Opened Short Pos on '.$currencyPair.' | Amount: '.$amount.'';
	$output .= $sendMailBody;		
	if($debug == 0) {
		$newDB->sendMail($sendEmailBody); 
	}

	echo $output;
}


?>