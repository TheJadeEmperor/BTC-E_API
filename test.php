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
	$output = $currentTime.' <br />';	
}
else {
	
}


foreach($tradesTable as $trade) {
		
		$id = $trade->id;
		$trade_currency = $trade->trade_currency;
		$trade_condition = $trade->trade_condition;
		$trade_price = $trade->trade_price;
		$trade_amount = $trade->trade_amount;
		$trade_exchange = $trade->trade_exchange;
		$trade_until = $trade->until;
		
		if($trade_exchange == 'Poloniex'){
			
			if($trade_currency == 'ETH/BTC') {
				$pair = 'BTC_ETH';
			}
			else if($trade_currency == 'ETH/USDT') {
				$pair = 'USDT_ETH';
			}
			
			
			
		}
		
		
		if($debug == 1) {
			
			$output .= $trade_exchange.' | '.$trade_currency.' | if '.$pair.' | is '.$trade_condition.' '.$trade_price.' then '.$trade_action.' '.$trade_amount.' | until '.$trade_until.'
			<br />';
		}
		else {
			$output .= $trade_exchange.' '.$trade_currency.' '.$pair."\n";
			
		}
	 
}



$pair = 'BTC_ETH';

$priceArray = $polo->get_ticker($pair);

$rate = $priceArray['last'];

//$rate = '0.03795556';
$amount = '0.009985';

//$result =  $polo->buy($pair, $rate, $amount); 
//$result =  $polo->sell($pair, $rate, $amount); 
print_r($result);

	echo $output;
	

?>