<?php
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'api_poloniex.php');
include($dir.'config.php');
include($dir.'api_imap_class.php');


//requires the extension php_openssl to work
$polo = new poloniex($polo_api_key, $polo_api_secret);

//connect to the BTC Database
$newDB = new Database($db);

$newDB->database($dbHost, $dbUser, $dbPW, $dbName);


$debug = 0; 

$currencyPair = 'BTC_DASH'; 
$amount = '0.4';
//get current price of pair
$currentRate = $polo->get_ticker($currencyPair);
$sellRate = $currentRate['highestBid'];
$buyRate = $currentRate['lowestAsk'];

$rate = $currentRate['last'];

echo 'currencyPair: '. $currencyPair.' | amount: '.$amount.' | buyRate: '.$buyRate.' | sellRate: '.$sellRate.'<br />'; 

//connect to imap service
$mails = new EmailImporter( '{host187.hostmonster.com:993/imap/ssl}INBOX', $gmail_username, $gmail_password);


//search for these subject lines
$subjectSignalLong = "TradingView Alert: DASHBTC Long Signal";
$subjectSignalShort = "TradingView Alert: DASHBTC Short Signal";

$matchedMailsLong = $mails->getMailsBySubject($subjectSignalLong);
$matchedMailsShort = $mails->getMailsBySubject($subjectSignalShort);

//subject line is found - long signal
if($matchedMailsLong[0]['subject'] == $subjectSignalLong) { 
	print_r($matchedMailsLong[0]['subject']);
	$criteria_is_met = true;
	$long_signal = true;
}
//subject line is found - short signal
if($matchedMailsShort[0]['subject'] == $subjectSignalShort) { 
	print_r($matchedMailsShort[0]['subject']);
	$criteria_is_met = true;
	$short_signal = true;
}

	
//check for open margin order & replace it
//to keep orders fresh and make sure orders go through
if($debug == 0) {
	$openOrders = $polo->return_open_orders($currencyPair);
		 
	echo 'openOrders '; 
	var_dump($openOrders);
	if($openOrders[0]['margin'] == 1) 
		$orderNumber = $openOrders[0]['orderNumber'];
	
	if(isset($orderNumber)) {
		$moveOrder = $polo->move_order($orderNumber, $sellRate);
		echo 'moveOrder ';
		var_dump($moveOrder);
	}
}


//check for open pos - poloniex
$getMarginPos = $polo->get_margin_position($currencyPair);
echo 'getMarginPos: ';

if($getMarginPos['type'] == 'none') { //no positions open
	echo ' none <br />';
}
else {
	var_dump($getMarginPos); 
	$orderNumber = $getMarginPos['orderNumber'];
}



if($criteria_is_met) {

	//close pos befor opening new pos 
	$closeMarginPos = $polo->close_margin_position($currencyPair, $marginOrderNumber);
	echo 'closeMarginPos: ';
	var_dump($closeMarginPos); 
	

	if($short_signal) { //open margin pos - short
		if($debug == 0) {
			$shortPos = $polo->margin_sell($currencyPair, $sellRate, $amount, 1);	
			var_dump($shortPos); 
		}
		echo $typePos = 'shortPos'; 
		$rate = $sellRate;
	}
	
	if ($long_signal) { //open margin pos - long
		if($debug == 0) { 
			$longPos = $polo->margin_buy($currencyPair, $buyRate, $amount, 1);
			var_dump($longPos);
		}
		echo $typePos = 'longPos';
		$rate = $buyRate;
	}
		
	//send text message
	$sendMailBody = 'Opened '.$typePos.' for '.$amount.' '.$currencyPair.' on Poloniex for '.$rate;

	
	if($debug == 0) {
		$newDB->sendMail($sendMailBody); 
	} 

	echo '<br />'.$sendMailBody;
}


?>