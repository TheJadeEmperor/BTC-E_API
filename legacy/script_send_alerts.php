<?php
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'api_poloniex.php');
include($dir.'api_bittrex.php');
include($dir.'config.php');
include($dir.'mysqli.php');

//set timezone
date_default_timezone_set('America/New_York');

global $db, $currencyDB, $currencyPolo;

$debug = $_GET['debug'];

$config = array(
	'host' => $dbHost,
	'user' => $dbUser,
	'pass' => $dbPW,
	'table' => $dbName,
);


//mysqli database
$db = new DB($config);


//connect to the BTC Database
$tableData = new Database($db);

//get rows from alerts table
$condTable = $tableData->getAlerts($debug);
 
//requires the extension php_openssl to work
$polo = new poloniex($polo_api_key, $polo_api_secret);

foreach($condTable as $arr => $cond) {
	 	
	$id = $cond['id'];
	$currencyDB = $cond['currency'];
	$onCondition = $cond['on_condition'];
	$onPrice = floatval($cond['price']);
	$sent = $cond['sent'];
	
	
	//format the currency for polo
	$pieces =  explode('/', $currencyDB);
	$currencyPolo = $pieces[1].'_'.$pieces[0];
	
	//get the live price from Polo
	$currentPrice = $polo->get_ticker($currencyPolo);
	$currentPrice = floatval($currentPrice['last']);
		
	//if conditions are right, send email and text
	if($onCondition == '>') {
		if($currentPrice >= $onPrice) {
			$result = 'true';
		}
		else {
			$result = 'false';
		}
	}
	else if ($onCondition == '<') {
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
		$resultA = $db->query($queryA);
	}
	
	
	if($debug == 1) {
		
		$output = ''.$currencyDB.' '.$onCondition.' '.number_format($onPrice, 2).' | '.$cond->exchange.' ('.$currencyPolo.') | Live price: '.number_format($currentPrice, 2).' | '.$result.' '.$extra .'<br /><br />';
		
	}
	else {
		$output = ''.$currencyDB.' '.$onCondition.' '.number_format($onPrice, 2).' | '.$cond->exchange.' ('.$currencyPolo.') | Live price: '.number_format($currentPrice, 2).' | '.$result.' '.$extra ."\n\n";
	}
	
	echo $output;		
}
 


exit;





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
	if($onCondition == '>') {
		if($currentPrice >= $onPrice) {
			$result = 'true';
		}
		else {
			$result = 'false';
		}
	}
	else if ($onCondition == '<') {
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
		
	}
	
	
	if($debug == 1) {
		
		$output = ''.$currencyDB.' '.$onCondition.' '.number_format($onPrice, 2).' | '.$cond->exchange.' ('.$currencyPolo.') | Live price: '.number_format($currentPrice, 2).' | '.$result.' '.$extra .'<br /><br />';
		
	}
	else {
		$output = ''.$currencyDB.' '.$onCondition.' '.number_format($onPrice, 2).' | '.$cond->exchange.' ('.$currencyPolo.') | Live price: '.number_format($currentPrice, 2).' | '.$result.' '.$extra ."\n\n";
	}
	
	echo $output;	
}

?>