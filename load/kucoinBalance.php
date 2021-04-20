<?php
$dir = '../include/';
include($dir.'functions.php');
include($dir.'config.php');
include($dir.'api_kucoin.php');

//set timezone
date_default_timezone_set('America/New_York');

if($_GET['accessKey'] != $accessKey) {
	echo "Wrong access key"; exit;
}


function displayKCTable($subAccount, $apiFields) {
	
	$apiKey = $apiFields['apiKey'];
	$apiSecret = $apiFields['apiSecret'];
	$passphrase = $apiFields['passphrase'];
	$url = $apiFields['url'];

	$output = '
	<table class="table">
	<thead class="thead-default">
	<tr>
		<th colspan="4"><a href="'.$url.'" target="_BLANK">'.$subAccount.'</a> <img src="include/images/refresh.png" class="clickable" onclick="javascript:'.$subAccount.'()" width="25px" /></th>
	</tr>
	<tr>
		<th>Currency</th><th>Balance</th><th>Price</th><th>USDT</th>
	</tr>
';


	$Kucoin = new Kucoin($apiKey, $apiSecret, $passphrase);
	$getBalances = $Kucoin->checkBalance();
    $totalBalance = 0;
  
    foreach($getBalances['data'] as $index) { //go through each coin you have
          $available = $index['available']; 
		  $currency = $index['currency'];

		  if($available > 0) { //check for available balance
				if($index['currency'] == 'USDT') {
					$USDTBalance = $available; 
					$totalBalance += $USDTBalance; //add to totalBalance
					$bid = 1;
				}
				else {
					$pair = $currency.'-USDT'; //XRP-USDT
					$getPrices = $Kucoin->getMarketPrice($pair); //get bid price
					$bid = $getPrices['data']['bestBid'];
					
					$USDTBalance = $available * $bid;
	  
					$totalBalance += $USDTBalance; //add to totalBalance
				}

				$output .= '<tr><td>'.$currency.'</td><td>'.$available.'</td><td>'.$bid.'</td><td>'.$USDTBalance.'</td></tr>';
          }
    }
  
    //show 4 decimals
    $coinBalance = number_format($coinBalance, 4, '.', '');
    $USDTBalance = number_format($USDTBalance, 4, '.', '');
    $totalBalance = number_format($totalBalance, 4, '.', '');
  
	return $output;
}

if ($_GET['page'] == 'kucoinMainBalance') {

	$apiFields = array(
		'apiKey' => $kucoin0_key,
		'apiSecret' => $kucoin0_secret,
		'passphrase' => $kucoin0_passphrase,
		'url' => 'https://trade.kucoin.com/KEY-BTC'
	);

	echo displayKCTable('kucoinMainBalance', $apiFields);

}
else if ($_GET['page'] == 'kucoin1Balance') {

	$apiFields = array(
		'apiKey' => $kucoin1_key,
		'apiSecret' => $kucoin1_secret,
		'passphrase' => $kucoin1_passphrase,
		'url' => 'https://www.kucoin.com/account-sub/assets/DWCTrading'
	);

	echo displayKCTable('kucoin1Balance', $apiFields);

}
else if ($_GET['page'] == 'kucoin2Balance') {

	$apiFields = array(
		'apiKey' => $kucoin2_key,
		'apiSecret' => $kucoin2_secret,
		'passphrase' => $kucoin2_passphrase,
		'url' => 'https://www.kucoin.com/account-sub/assets/DWCTrading2'
	);

	echo displayKCTable('kucoin2Balance', $apiFields);
	
}
else if ($_GET['page'] == 'kucoin3Balance') {

	$apiFields = array(
		'apiKey' => $kucoin3_key,
		'apiSecret' => $kucoin3_secret,
		'passphrase' => $kucoin3_passphrase,
		'url' => 'https://www.kucoin.com/account-sub/assets/DWCTrading3'
	);

	echo displayKCTable('kucoin3Balance', $apiFields);

}
else if ($_GET['page'] == 'kucoin4Balance') {
	$apiFields = array(
		'apiKey' => $kucoin4_key,
		'apiSecret' => $kucoin4_secret,
		'passphrase' => $kucoin4_passphrase,
		'url' => 'https://www.kucoin.com/account-sub/assets/DWCTrading4'
	);

	echo displayKCTable('kucoin4Balance', $apiFields);
}
else if ($_GET['page'] == 'kucoin5Balance') {
	$apiFields = array(
		'apiKey' => $kucoin5_key,
		'apiSecret' => $kucoin5_secret,
		'passphrase' => $kucoin5_passphrase,
		'url' => 'https://www.kucoin.com/account-sub/assets/DWCTrading5'
	);

	echo displayKCTable('kucoin5Balance', $apiFields);
}

?>