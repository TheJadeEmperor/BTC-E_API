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

$debug = $_GET['debug'];

//database connection
$db = new ezSQL_mysql($dbUser, $dbPW, $dbName, $dbHost);

//connect to the BTC Database
$tableData = new Database($db);

//get all records from the alerts table
$tradesTable = $tableData->tradesTable();


//connect to Poloniex
$polo = new poloniex($polo_api_key, $polo_api_secret);

 //account balances
$balanceArray = $polo->get_balances();



if($debug == 1) {
	$newline = '<br />';
}
else {
	$newline = "\n";
}

$output = 'Current Time: '.$currentTime.' ('.time().')'.$newline.$newline;	


foreach($tradesTable as $trade) {
		
	$trade_id = $trade->id;
	$trade_exchange = $trade->trade_exchange;
	$trade_currency = $trade->trade_currency;
	$trade_condition = $trade->trade_condition;
	$trade_price = $trade->trade_price;
	$trade_action = $trade->trade_action;
	$trade_amount = $trade->trade_amount;
	$trade_result = $trade->result;
	$trade_unit = $trade->trade_unit;
	$trade_until = $trade->until_date.' '.$trade->until_time;
	
	if($trade_exchange == 'Poloniex') { //set the currency in poloniex
		
		list($coin, $market) = explode('/', $trade_currency); // XXX/BTC
		$pair = $market.'_'.$coin; //currency format is BTC_XXX
	}
	
	$output .= $coin.' | '.$balanceArray[$coin].' | ';
	
	if(!isset($balanceArray[$coin])) {//cannot read balance
		$output .= "Can't connect to Poloniex"; 
		echo $output; 
		exit;
	}
 
	if($balanceArray[$coin] > 0.1) {
		//make sure tradeAmt matches balance amount 
		$updateAmt = "UPDATE $tradeTable set trade_amount='".$balanceArray[$coin]."' WHERE trade_currency='".$trade_currency."'";
		
		$success = $db->query($updateAmt); 
		
		$output .= $updateAmt.$newline;
	}
	else if ($balanceArray[$coin] < 0.1) { //balance < 0.1
	
		//delete trades that has no balance and trade_action='Sell'
		//can't sell what you don't have
		$deleteOld = "DELETE FROM $tradeTable WHERE trade_currency = '".$trade->trade_currency."' AND trade_action='Sell'";
		
		$success = $db->query($deleteOld); 
		
		$output .=  $deleteOld.$newline;
	}
	
	
		
	//check timestamp
	$dbTimestamp = strtotime($trade_until);
	
	if($dbTimestamp >= time()) { //has trade expired yet
		$isExpired = 'No';
			
		$priceArray = $polo->get_ticker($pair); 

		$lastPrice = $priceArray['last']; //most recent price for this coin
		$percentChange = $priceArray['percentChange'] * 100;
		$percentChangeDisplay = $percentChange.'%';
		
		$lastPriceDisplay = $lastPrice;
		$tradePriceDisplay = $trade_price;

		if($trade_unit == 'BTC') {
			
			//check if price meets conditions
			if($trade_condition == '>' && $lastPrice >= $trade_price) {
				
				$isTradeable = 'true'; 
			}
			else if ($trade_condition == '<' && $lastPrice <= $trade_price) {
				
				$isTradeable =  'true';
			}
			else {
				$isTradeable = 'false';
			}
		}
		else { //$trade_unit == %
			
			if($trade_condition == '>' && $percentChange >= $trade_price) {
				$isTradeable = 'true'; 
			}
			else if($trade_condition == '<' && $percentChange <= $trade_price) {
				$isTradeable = 'true'; 
			}
			else {
				$isTradeable = 'false';
			}
			
			$trade_price = $lastPrice;		
		}
			
		
		if($isTradeable == 'true') {

			if($trade_action == 'Buy') {
				$tradeResult = $polo->buy($pair, $lastPrice, $trade_amount, 'immediateOrCancel'); 
			}
			else { 
				 for ($i = 0; $i <= 7; $i++) {
					$tradeResult = $polo->sell($pair, $lastPrice, $trade_amount, 'immediateOrCancel'); 
					//echo '<br />'.$i.' '.($tradeResult['amountUnfilled']).'<br />';
					 
					print_r($tradeResult);
					
					if($tradeResult['amountUnfilled'] == 0) break;
				}
			}
				
			//update trades table with result
			$update = "UPDATE $tradeTable SET
			result = '1' WHERE id = '".$trade_id."'";
			
			$success = $db->query($update); 

		}
	}
	else { //trade expired 
		$isExpired = 'Yes';	$isTradeable = 'False';
	} 
	
	print_r($tradeResult);
	
		
	$output .= $newline.$trade_exchange.' | '.$trade_currency.' | if '.$pair.' is '.$trade_condition.' '.$tradePriceDisplay.' '.$trade_unit.' then '.$trade_action.' '.$trade_amount.' | last price: '.$lastPriceDisplay.' | percentChange: '.$percentChangeDisplay.$newline.' valid until '.$trade_until.' | expired: '.$isExpired.' | isTradeable: '.$isTradeable.' '.$newline.$newline; 		
}

echo $output;

?>