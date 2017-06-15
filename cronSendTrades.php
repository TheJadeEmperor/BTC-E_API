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

//database connection
$db = new ezSQL_mysql($dbUser, $dbPW, $dbName, $dbHost);


$debug = $_GET['debug'];

//connect to the BTC Database
$tableData = new Database($db);

//connect to Poloniex
$polo = new poloniex($polo_api_key, $polo_api_secret);

//get all records from the alerts table
$tradesTable = $tableData->tradesTable();

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
		
		list($first, $second) = explode('/', $trade_currency);
		$pair = $second.'_'.$first; //currency format is xxx_yyy
	}
		
	//check timestamp
	$dbTimestamp = strtotime($trade_until);
	
	if($dbTimestamp >= time()) { //has trade expired yet
		$isValid = ' active';
			
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
			

		$queryT = "SELECT result from $tradeTable WHERE id='".$trade_id."'";
		$resultT = $db->get_results($queryT);
	
		$dbResult = $resultT[0]->result;
		
		if($isTradeable == 'true') {
			
			if($dbResult != 1) { //only trade once 
				if($trade_action == 'Buy')
					$tradeResult = $polo->buy($pair, $trade_price, $trade_amount); 
				else 
					$tradeResult = $polo->sell($pair, $trade_price, $trade_amount); 
				
				//update trades table with result
				$update = "UPDATE $tradeTable SET
				result = '1' WHERE id = '".$trade_id."'";
				
				$success = $db->query($update); 
				
				$isValidOnce = ' | true';
			}
			else { //trade already processed
				$isValidOnce = ' | false';
			}
		}
	}
	else { //trade expired 
		$isValid = $isTradeable = ' expired';
	} 
		
	$output .= $trade_exchange.' | '.$trade_currency.' | if '.$pair.' is '.$trade_condition.' '.$tradePriceDisplay.' '.$trade_unit.' then '.$trade_action.' '.$trade_amount.' | last price: '.$lastPriceDisplay.' | percentChange: '.$percentChangeDisplay.$newline.' valid until '.$trade_until.' | isValid: '.$isValid.' | isTradeable: '.$isTradeable.' '.$isValidOnce.'  '.$newline.$newline; 		
}


echo $output;

print_r($tradeResult);
	
?>