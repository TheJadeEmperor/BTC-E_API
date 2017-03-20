<?php
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'api_poloniex.php');
include($dir.'config.php');
include($dir.'ez_sql_core.php');
include($dir.'ez_sql_mysql.php');

//set timezone
date_default_timezone_set('America/New_York');


//get timestamp
$currentTime = date('Y-m-d H:i:s', time());



global $db;

$db = new ezSQL_mysql($dbUser, $dbPW, $dbName, $dbHost);

$debug = $_GET['debug'];

//connect to the BTC Database
$tableData = new Database($db);

//
$polo = new poloniex($polo_api_key, $polo_api_secret);

//get all records from the alerts table
$tradesTable = $tableData->tradesTable();

if($debug == 1){
	$output = 'Current Time: '.$currentTime.' ('.time().') <br /><br />';	
}
else {
	$output = 'Current Time: '.$currentTime.' <br /><br />';
}


foreach($tradesTable as $trade) {
		
		$id = $trade->id;
		$trade_exchange = $trade->trade_exchange;
		$trade_currency = $trade->trade_currency;
		$trade_condition = $trade->trade_condition;
		$trade_price = $trade->trade_price;
		$trade_action = $trade->trade_action;
		$trade_amount = $trade->trade_amount;
		$trade_until = $trade->until;
		
		if($trade_exchange == 'Poloniex'){
			
			if($trade_currency == 'ETH/BTC') {
				$pair = 'BTC_ETH';
			}
			else if($trade_currency == 'ETH/USDT') {
				$pair = 'USDT_ETH';
			}
			
		}
		
		//check timestamp
		$dbTimestamp = strtotime($trade_until);
		
		
		if($dbTimestamp >= time()) { //trade valid
			$valid = ' valid';
			
			$priceArray = $polo->get_ticker($pair);

			$lastPrice = $priceArray['last'];
			
			//check if price meets conditions
			if($trade_condition == '>=') {
				if($lastPrice >= $trade_price) {
					$result = 'true'; 
				}
				else {
					$result = 'false';
				}
			}
			else if ($trade_condition == '<=') {
				if($lastPrice <= $trade_price) {
					$result =  'true';
				}
				else {
					$result = 'false';
				}
			}
			else {
				$result =  'error';
			}

			if($result == 'true') {
				if($trade_action == 'Buy')
					$tradeResult = $polo->buy($pair, $trade_price, $trade_amount); 
				else 
					$tradeResult = $polo->sell($pair, $trade_price, $trade_amount); 
			}
		}
		else { //trade expired 
			$valid = ' expired';
		}
		
		
		if($debug == 1) {
			
			$output .= $trade_exchange.' | '.$trade_currency.' | if '.$pair.' | is '.$trade_condition.' '.$trade_price.' then '.$trade_action.' '.$trade_amount.' units | valid until '.$trade_until.'
			'.$valid.' | '.$result.'
			<br />'.$trade_until.' ('.$dbTimestamp.')<br />';
		}
		else {
			$output .= $trade_exchange.' | '.$trade_currency.' | if '.$pair.' | is '.$trade_condition.' '.$trade_price.' then '.$trade_action.' '.$trade_amount.' units | valid until '.$trade_until.'
			'.$valid.' | '.$result."\n".$trade_until.' ('.$dbTimestamp.')'."\n";
		}	 
}


echo $output;

print_r($tradeResult);
	
?>