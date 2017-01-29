<?php
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'api_poloniex.php');
include($dir.'config.php');
include($dir.'ez_sql_core.php');
include($dir.'ez_sql_mysql.php');

global $db, $currencyDB, $currencyPolo;

$db = new ezSQL_mysql($dbUser, $dbPW, $dbName, $dbHost);


//connect to the BTC Database
$tableData = new Database($db);

//requires the extension php_openssl to work
$polo = new poloniex();

//get all records from the alerts table
$condTable = $tableData->alertsTable();


foreach($condTable as $cond) {
	
	//format the currency for polo
	$currencyDB = $cond->currency;
	$onCondition = $cond->on_condition;
	$onPrice = floatval($cond->price);
	
	$pieces =  explode('/', $currencyDB);
	
	$currencyPolo = $pieces[1].'_'.$pieces[0];
	
	//get the live price from Polo
	$currentPrice = $polo->get_ticker($currencyPolo);
	$currentPrice = floatval($currentPrice['last']);
	
	
	
	//if conditions are right, send email and text
	if($onCondition == '>=') {
		if($currentPrice >= $onPrice) {
			echo 'true';
		}
		else {
			echo 'false';
		}
	}
	else if ($onCondition == '<=') {
		if($currentPrice <= $onPrice) {
			echo 'true';
		}
		else {
			echo 'false';
		}
	}
	else {
		echo 'error';
	}
	
	echo '<br />'.$currencyDB.' ('.$currencyPolo.') '.$onCondition.' '.$onPrice.' | 
	'.$cond->exchange.' | Live price: '.$currentPrice.' | '.$result.'<br />';
}




//$tableData->sendMail();


//new field to db: sent 
//update to Y if sent 

?>