<?php
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'api_poloniex.php');
include($dir.'config.php');
include($dir.'ez_sql_core.php');
include($dir.'ez_sql_mysql.php');

//set timezone
date_default_timezone_set('America/New_York');

global $db;
global $currentTime;

//get timestamp
$currentTime = date('Y-m-d H:i:s', time());

//database connection

$db = new ezSQL_mysql($dbUser, $dbPW, $dbName, $dbHost);

$debug = $_GET['debug'];

//connect to Poloniex
$polo = new poloniex($polo_api_key, $polo_api_secret);


$tickerArray = $polo->get_ticker(); //ticker prices
$balanceArray = $polo->get_balances(); //account balances

//print_r($balanceArray);

foreach($tickerArray as $currencyPair => $tickerData) {
	
	$percentChange = $tickerData['percentChange'];
	$lastPrice = $tickerData['last']; //most recent price for this coin
	
	list($market, $curr) = explode('_',  $currencyPair);
	$dbCurrencyPair = $curr.'/'.$market;
	
	$percentChangeFormat = $percentChange * 100;
	
	$percentChangeFormat = number_format($percentChangeFormat, 2);
	
	if($market == 'BTC') //only show BTC markets
	if($percentChangeFormat > 15 && $percentChangeFormat < 20) { //check if price > 15% && price < 20%

		
		//check if there's a balance for the currencyPair
		if($balanceArray[$curr] == 0) { //if no balance, then buy
			$balanceDisplay = ' No balance ';
			
			//echo $lastPrice .' ';
			$tradeAmount = 0.1 / $lastPrice;

			$dateInTwoWeeks = strtotime('+2 weeks');		
			$until = date('Y-m-d h:i:m', $dateInTwoWeeks);
			
			
			//buy order
			if($debug != 1) {
				$tradeResult = $polo->buy($currencyPair, $lastPrice, $tradeAmount); 
			}
			
			//set stop loss through btc_trades table - sell if < 12%
			$insert = "INSERT INTO $tradeTable (trade_exchange, trade_currency, trade_condition, trade_price, trade_action, trade_amount, trade_unit, until) values ('Poloniex', '".$dbCurrencyPair."', '<', '12', 'Sell', '".$tradeAmount."', '%', '".$until."' )";
			
			//create new record in trade table for currencyPair
			if($debug != 1) {
				 $success = $db->query($insert);
				if($success == 1) 
					echo '<br />Added record '.$insert.'<br />';
				else 
					echo '<br />Failed to add record '.$insert.'<br />';
			}
			
		}
		else { //there is a balance
			$balanceDisplay = $balanceArray[$curr];
		}
		
		echo $output = '<br />'.$currencyPair.' +'.$percentChangeFormat.'% | '.$balanceDisplay .' | lastPrice: '.$lastPrice.' | tradeAmount: '.$tradeAmount;
	}
	
}


print_r($tradeResult);
?>