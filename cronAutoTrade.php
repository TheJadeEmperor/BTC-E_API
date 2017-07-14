<?php
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'api_poloniex.php');
include($dir.'config.php');
include($dir.'ez_sql_core.php');
include($dir.'ez_sql_mysql.php');

//set timezone
date_default_timezone_set('America/New_York');

$debug = $_GET['debug'];

global $db;
global $currentTime;

//get timestamp
$currentTime = date('Y-m-d H:i:s', time());

//database connection
$db = new ezSQL_mysql($dbUser, $dbPW, $dbName, $dbHost);

//connect to Poloniex
$polo = new poloniex($polo_api_key, $polo_api_secret);

$tickerArray = $polo->get_ticker(); //ticker prices
$balanceArray = $polo->get_balances(); //account balances

if($debug == 1) {
	$newline = '<br />';
}
else {
	$newline = "\n";
}


foreach($tickerArray as $currencyPair => $tickerData) {
	
	$percentChange = $tickerData['percentChange'];
	$lastPrice = $tickerData['last']; //most recent price for this coin
	
	list($market, $curr) = explode('_',  $currencyPair);
	$dbCurrencyPair = $curr.'/'.$market;
	
	//only show BTC markets
	if($market != 'BTC') continue; 

	//useless coins
	if($curr == 'SJCX' || $curr == 'LBC' || $curr == 'VTC') continue;
	
	
	$percentChangeFormat = $percentChange * 100;
	$percentChangeFormat = number_format($percentChangeFormat, 2);

	$stopLoss = $lastPrice - $lastPrice * 0.10; //10% below entry point
	$stopLoss = number_format($stopLoss, 8);
	
	$balanceDisplay = $balanceArray[$curr];
	
	
	//check for existing Stop Loss trade
	$selectCount = "SELECT count(*) as count from $tradeTable WHERE trade_currency='".$dbCurrencyPair."'";
	$resultCount = $db->get_results($selectCount);
	
	$recordCount = $resultCount[0]->count;
	

	$tradeAmount = 0.05 / $lastPrice; //0.05 btc 
	$tradeAmount = number_format($tradeAmount, 8, '.', '');

	//minus trading fees
	$tradeAmountAfterFees = $tradeAmount - ($tradeAmount * 0.0015);
	
		
	$dateInTwoWeeks = strtotime('+2 weeks');		
	$until = date('Y-m-d h:i:m', $dateInTwoWeeks);
	
	//missing a stop loss trade
	if($recordCount == 0 && $balanceArray[$curr] > 0.1) {

		//set stop loss through btc_trades table 
		$insert = "INSERT INTO $tradeTable (trade_exchange, trade_currency, trade_condition, trade_price, trade_action, trade_amount, trade_unit, until) values ('Poloniex', '".$dbCurrencyPair."', '<', '".$stopLoss."', 'Sell', '".$tradeAmountAfterFees."', 'BTC', '".$until."' )";
		
		if(isset($dbCurrencyPair) && isset($tradeAmountAfterFees))
			$success = $db->query($insert); //create new record in trade table for currencyPair
		
		$recordCount = 1;
	}
	
	
	if($percentChangeFormat < -16 || ($percentChangeFormat > 16 && $percentChangeFormat < 20)) {

		//check if there's a balance & SL trade for the currencyPair
		if($balanceArray[$curr] <= 0.2 && $recordCount == 0) { 
			$balanceDisplay = ' No balance ';
		
		exit; 
		
			//buy order
			if($debug != 1) {
				$tradeResult = $polo->buy($currencyPair, $lastPrice, $tradeAmount, 'immediateOrCancel'); 
			
				//set stop loss through btc_trades table 
				$insert = "INSERT INTO $tradeTable (trade_exchange, trade_currency, trade_condition, trade_price, trade_action, trade_amount, trade_unit, until) values ('Poloniex', '".$dbCurrencyPair."', '<', '".$stopLoss."', 'Sell', '".$tradeAmountAfterFees."', 'BTC', '".$until."' )";
				
				if(isset($dbCurrencyPair) && isset($tradeAmountAfterFees))
					$success = $db->query($insert); //create new record in trade table for currencyPair
				
				if($success == 1) 
					echo $newline.'Added record '.$insert.$newline;
				else 
					echo $newline.'Failed to add record '.$insert. $newline;
			}
		}
		
		echo $output = $currencyPair.' '.$percentChangeFormat.'% | '.$balanceDisplay .' | lastPrice: '.$lastPrice.' | stopLoss: '.$stopLoss.' | tradeAmount: '.$tradeAmount.' | tradeAmountAfterFees: '.$tradeAmountAfterFees.$newline.$newline;
	}
}


print_r($tradeResult);
?>