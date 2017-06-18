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

//print_r($balanceArray);

foreach($tickerArray as $currencyPair => $tickerData) {
	
	$percentChange = $tickerData['percentChange'];
	$lastPrice = $tickerData['last']; //most recent price for this coin
	
	list($market, $curr) = explode('_',  $currencyPair);
	$dbCurrencyPair = $curr.'/'.$market;
	
	$percentChangeFormat = $percentChange * 100;
	
	$percentChangeFormat = number_format($percentChangeFormat, 2);

	$stopLoss = $lastPrice - $lastPrice * 0.02;
	
	if($market == 'BTC') //only show BTC markets
	if($percentChangeFormat > 15 && $percentChangeFormat < 20) { //check if price > 15% && price < 20%

		$tradeAmount = 0.1 / $lastPrice;

		//minus trading fees
		$tradeAmountAfterFees = $tradeAmount - $tradeAmount * 0.0015;
			
		//check if there's a balance for the currencyPair
		if($balanceArray[$curr] <= 0.5) { //if no balance, then buy
			$balanceDisplay = ' No balance ';
			
			$dateInTwoWeeks = strtotime('+2 weeks');		
			$until = date('Y-m-d h:i:m', $dateInTwoWeeks);
					
			//buy order
			if($debug != 1) {
				$tradeResult = $polo->buy($currencyPair, $lastPrice, $tradeAmount); 
			
				//set stop loss through btc_trades table 
				$insert = "INSERT INTO $tradeTable (trade_exchange, trade_currency, trade_condition, trade_price, trade_action, trade_amount, trade_unit, until) values ('Poloniex', '".$dbCurrencyPair."', '<', '".$stopLoss."', 'Sell', '".$tradeAmountAfterFees."', 'BTC', '".$until."' ) LIMIT 1";
				
				if(isset($dbCurrencyPair) && isset($tradeAmountAfterFees))
					$success = $db->query($insert); //create new record in trade table for currencyPair
				
				if($success == 1) 
					echo $newline.'Added record '.$insert.$newline;
				else 
					echo $newline.'Failed to add record '.$insert. $newline;
			}
		}
		else { //there is a balance
			$balanceDisplay = $balanceArray[$curr];
		}
		
		echo $output = $currencyPair.' +'.$percentChangeFormat.'% | '.$balanceDisplay .' | lastPrice: '.$lastPrice.' | stopLoss: '.$stopLoss.' | tradeAmount: '.$tradeAmount.' | tradeAmountAfterFees: '.$tradeAmountAfterFees.$newline.$newline;
	}
	
}


print_r($tradeResult);
?>