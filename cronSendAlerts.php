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
	
	$id = $cond->id;
	$currencyDB = $cond->currency;
	$onCondition = $cond->on_condition;
	$onPrice = floatval($cond->price);
	$sent = $cond->sent;
	
	//format the currency for polo
	$pieces =  explode('/', $currencyDB);
	
	$currencyPolo = $pieces[1].'_'.$pieces[0];
	
	//get the live price from Polo
	$currentPrice = $polo->get_ticker($currencyPolo);
	$currentPrice = floatval($currentPrice['last']);
	
	
	
	//if conditions are right, send email and text
	if($onCondition == '>=') {
		if($currentPrice >= $onPrice) {
			$result = 'true';
		}
		else {
			$result = 'false';
		}
	}
	else if ($onCondition == '<=') {
		if($currentPrice <= $onPrice) {
			$result =  'true';
		}
		else {
			$result = 'false';
		}
	}
	else {
		$result =  'error';
	}
	 
	
	$sendEmailBody = $currencyDB.' is '.$onCondition.' '.number_format($onPrice, 2).' | Live price: '.number_format($currentPrice, 2);
	
	if($result == 'true') {
		
		//check if email is already sent (don't spam the same email over and over)
		if($sent == 'Yes') {
			$extra = ' | already sent';
		}
		else {
			$extra = ' | will send ';
			$success = $tableData->sendMail($sendEmailBody);
		}
		
		$queryA = 'UPDATE '.$tableName.' SET sent = "Yes" WHERE id='.$id;
		$resultA = $db->query($queryA); //$db->debug();
		
		echo $success;
		
		$error = error_get_last();
		echo $error["message"];
		
		$tableData->showMail();
	}
	
	
	$output = '<br />'.$currencyDB.' ('.$currencyPolo.') '.$onCondition.' '.number_format($onPrice, 2).' | 
	'.$cond->exchange.' | Live price: '.number_format($currentPrice, 2).' | '.$result.' '.$extra .'<br />';
	
	echo $output;
	
}




?>