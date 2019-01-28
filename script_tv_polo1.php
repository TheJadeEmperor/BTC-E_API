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


$debug = 1; 

$currencyPair = 'USDT_BTC'; 
$amount = '0.009';
$orderType = 'openOrder';

//get current price of pair
$currentRate = $polo->get_ticker($currencyPair);

$sellRate = $currentRate['highestBid'];
$buyRate = $currentRate['lowestAsk'];


echo 'currencyPair: '. $currencyPair.' | amount: '.$amount.' | buyRate: '.$buyRate.' | sellRate: '.$sellRate.'<br />'; 


//connect to imap service
$mails = new EmailImporter( '{host187.hostmonster.com:993/imap/ssl}INBOX', $gmail_username, $gmail_password);


//search for these subject lines
$subjectSignalLong = "TradingView Alert: XBTUSD Long Signal";
$subjectSignalShort = "TradingView Alert: XBTUSD Short Signal";

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

	
//check for open order & replace it using move_order()
//to keep orders fresh and make sure orders go through
if($debug == 0) {
	$openOrders = $polo->return_open_orders($currencyPair);
		 
	echo 'openOrders '; 
	
	var_dump($openOrders);
	
	foreach($openOrders as $num => $order) {
		$orderNumber = $order['orderNumber'];
		$orderType = $order['orderType'];
		
		if($orderType == 'sell' || $orderType == 'buy') {
			$moveOrder = $polo->move_order($orderNumber, $buyRate);
			echo 'moveOrder ';
			var_dump($moveOrder);
		}
	}	 
}


if($criteria_is_met) {

	if($short_signal) { //open pos - short
		if($debug == 0) {
			$shortPos = $polo->sell($currencyPair, $sellRate, $amount, $orderType);
			
			var_dump($shortPos); 
		}
		echo $typePos = 'Sell '; 
		$rate = $sellRate;
	}
	
	if ($long_signal) { //open pos - long
		if($debug == 0) {
			$longPos = $polo->buy($currencyPair, $buyRate, $amount, $orderType);
			
			var_dump($longPos);
		}
		echo $typePos = 'Buy ';
		$rate = $buyRate;
	}
		
	//send text message
	$sendMailBody = 'Action: '.$typePos.' '.$amount.' of '.$currencyPair.' on Poloniex for '.$rate;

	
	if($debug == 0) {
		$newDB->sendMail($sendMailBody); 
	}  

	echo '<br />'.$sendMailBody;
}


?>