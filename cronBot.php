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

//connect to Poloniex
$polo = new poloniex($polo_api_key, $polo_api_secret);


$tickerArray = $polo->get_ticker(); //ticker prices
$balanceArray = $polo->get_balances(); //account balances

//print_r($balanceArray);

foreach($tickerArray as $currencyPair => $tickerData) {
	$percentChange = $tickerData['percentChange'];
	
	list($crap, $curr) = explode('_',  $currencyPair);
	
	$percentChangeFormat = $percentChange * 100;
	
	$percentChangeFormat = number_format($percentChangeFormat, 2);
	
	if($crap == 'BTC') //only show BTC markets
	if($percentChangeFormat > 15 && $percentChangeFormat < 20) { //check if price > 15% && price < 20%
		
		echo '<br />'.$currencyPair.' <br />'.$percentChangeFormat.'% <br />';
		
		//check if there's a balance for the currencyPair
		echo $balanceArray[$curr];
		if($balanceArray[$curr] == 0) {
			echo 'empty';
		}
	}
	
	
}


//check all polo prices

/*


if there is no balance, buy 0.1 BTC

create new record in trade table for currencyPair
sell if < 6%

*/




?>