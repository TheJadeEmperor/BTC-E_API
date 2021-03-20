<?php
$dir = '../include/';
include($dir.'functions.php');
include($dir.'config.php');
include($dir.'api_kucoin.php');

//set timezone
date_default_timezone_set('America/New_York');

if($_GET['accessKey'] != 'AllLivesMatter') {
	echo "Wrong access key"; exit;
}

function displayKCBalance($getBalances) {
	$totalBalance = 0;

	foreach($getBalances['data'] as $index) { //go through each coin you have
        $available = $index['available'];
    
        if($index['currency'] == $coin[1] && $available > 0) { //match coin symbol   
            $coinBalance = $available; 
            if($index['available'] > 0) { //check for available balance
                $sellQT = $index['available']; 
                $totalBalance += $sellQT * $bid;
            }
        }

        if($index['currency'] == 'USDT' && $available > 0) {
            $USDTBalance = $available; 
            $totalBalance += $USDTBalance; //add to totalBalance
            $buyQT = $USDTBalance/$ask; //quantity to buy
            $buyQT = $buyQT * $percentBalance; 
        }
	}

	//orders only take 4 decimals
	$buyQT = number_format($buyQT, 4, '.', '');
	$sellQT = number_format($sellQT, 4, '.', '');
	$coinBalance = number_format($coinBalance, 4, '.', '');
	$USDTBalance = number_format($USDTBalance, 4, '.', '');
	$totalBalance = number_format($totalBalance, 4, '.', '');

	echo $USDTBalance.' '.$totalBalance;

}

if ($_GET['page'] == 'kucoinMainBalance') {
	
	$key = $kucoin1_key;
	$secret = $kucoin1_secret;
	$passphrase = $kucoin1_passphrase;   
	
	$getBalances = checkBalance();

    displayKCBalance($getBalances);
exit;
	$totalBalance = 0;

	foreach($getBalances['data'] as $index) { //go through each coin you have
    $available = $index['available'];
   
	if($index['currency'] == $coin[1] && $available > 0) { //match coin symbol   
			$coinBalance = $available; 
			if($index['available'] > 0) { //check for available balance
				$sellQT = $index['available']; 
				$totalBalance += $sellQT * $bid;
			}
		}

		if($index['currency'] == 'USDT' && $available > 0) {
			$USDTBalance = $available; 
			$totalBalance += $USDTBalance; //add to totalBalance
			$buyQT = $USDTBalance/$ask; //quantity to buy
			$buyQT = $buyQT * $percentBalance; 
		}
	}

	//orders only take 4 decimals
	$buyQT = number_format($buyQT, 4, '.', '');
	$sellQT = number_format($sellQT, 4, '.', '');
	$coinBalance = number_format($coinBalance, 4, '.', '');
	$USDTBalance = number_format($USDTBalance, 4, '.', '');
	$totalBalance = number_format($totalBalance, 4, '.', '');

	echo $USDTBalance.' '.$totalBalance;
}
else if ($_GET['page'] == 'kucoin1Balance') {
	$key = $kucoin1_key;
	$secret = $kucoin1_secret;
	$passphrase = $kucoin1_passphrase;   
	
	$getBalances = checkBalance();

}
else if ($_GET['page'] == 'kucoin2Balance') {

	$key = $kucoin2_key;
	$secret = $kucoin2_secret;
	$passphrase = $kucoin2_passphrase;   
	
	$getBalances = checkBalance();
	
}
else if ($_GET['page'] == 'kucoin2Balance') {

    $key = $kucoin3_key;
	$secret = $kucoin3_secret;
	$passphrase = $kucoin3_passphrase;   
	
	$getBalances = checkBalance();
}
else if ($_GET['page'] == 'kucoin2Balance') {

    $key = $kucoin4_key;
	$secret = $kucoin4_secret;
	$passphrase = $kucoin4_passphrase;   
	
	$getBalances = checkBalance();
}
else if ($_GET['page'] == 'kucoin2Balance') {

    $key = $kucoin5_key;
	$secret = $kucoin5_secret;
	$passphrase = $kucoin5_passphrase;   
	
	$getBalances = checkBalance();
}

?>