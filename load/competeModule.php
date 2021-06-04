<?php
$dir = '../include/';
include($dir.'api_database.php');
include($dir.'api_gate.php');
include($dir.'api_kucoin.php');
include($dir.'api_bittrex.php');
include($dir.'functions.php');
include($dir.'config.php');

if($_GET['accessKey'] != $accessKey) {
	echo "Wrong access key"; exit;
}

//get gate balance
$Gate = new Gate($gate_key, $gate_secret);
$getBalances = $Gate->getBalances();

//connect to kc1 kc2 kc3 get balances 
$KC1 = new Kucoin($kucoin1_key, $kucoin1_secret, $kucoin1_passphrase);
$KC2 = new Kucoin($kucoin2_key, $kucoin2_secret, $kucoin2_passphrase);
$KC3 = new Kucoin($kucoin3_key, $kucoin3_secret, $kucoin3_passphrase);

$pair = 'KCS-USDT';
$getKCS = $KC3->getMarketPrice($pair);
$bidKCS['KCS_USDT'] = $getKCS['data']['bestBid'];

function format_balance($balance) {
	$balance = number_format($balance, 4, '.', ',');
	$balance = '$'.$balance;

	return $balance;
}

function getKCSubBalance ($class, $thisCurrency) {
	
	$getBalances = $class->checkBalance();
//echo'<pre>';print_r($getBalances);

	foreach($getBalances['data'] as $index) { 
		$available = $index['available'];
		$currency = $index['currency'];
		if ($currency == $thisCurrency && $available > 0) {
			//echo ''.$currency.' '.$available.' | ';
			return $available;
		}
	}
}

$KC1Balance = getKCSubBalance($KC1, 'KCS');
$KC2Balance = getKCSubBalance($KC2, 'ADA');
$KC3Balance = getKCSubBalance($KC3, 'KCS');

$currencyPairs = array(
	'ADA_USDT', 'VET_USDT', 'MATIC_USDT', 'DOGE_USDT', 'BTC_USDT'
);

foreach($currencyPairs as $pair) {
	$getMarketPrice = $Gate->getMarketPrice($pair);
	$bidKCS[$pair] = $getMarketPrice[0]['highest_bid'];
	//echo $bid[$pair].' ';
}


//get bittrex balance
$bittrex = new Client ($bittrex_api_key, $bittrex_api_secret);
$balanceBittrex = $bittrex->getBalances();

if(!empty($balanceBittrex)) { 
	foreach($balanceBittrex as $key => $val) {
	
		$currency = $val->Currency; //coin 
		$currencyBalance = $val->Balance; //coin balance

		//$btcTicker = $bittrex->getTicker('USDT-BTC');
		//$btcPrice = $btcTicker->Last;
		
		if($currency == 'BTXCRD') continue; //invalid market
		if ($currency == 'USDT') { 
			$lastFormat = 1; //usdt has no price
			$usdtValue = $currencyBalance; 
		}
		else { //all other coins 
			$currencyPair = 'USDT-'.$currency; //ticker pair
			$coinTicker = $bittrex->getTicker($currencyPair);

			$lastFormat = $coinTicker->Last;
			$usdtValue =  $currencyBalance * $lastFormat; //get usdt value
		}
	
		$usdtValueFormat = number_format($usdtValue, 2);
		$totalUSD += $usdtValue;
		
	} //foreach($balance as $x => $val)
	 
	$totalBTCFormat	= number_format($totalBTC, 4);
	$totalUSDTFormat = number_format($totalUSD, 2);
	// echo '<'.$totalUSDTFormat.'>';
} //if(!empty($balance))


//get gate balance 
foreach($getBalances as $index) {
    $currency = $index['currency'];
    $available = $index['available'];
    $locked = $index['locked'];

    if($available > 0) { //check for available balance
        if($currency == 'USDT') {
            $USDTBalance = $available; 
            $totalBalance += $USDTBalance; //add to totalBalance
            $bid = 1;
        }
        else {
            $pair = $currency.'_USDT'; //XRP-USDT
            $getMarketPrice = $Gate->getMarketPrice($pair);
            $bid = $getMarketPrice[0]['highest_bid'];
            
            $USDTBalance = $available * $bid;

            $totalBalance += $USDTBalance; //add to totalBalance
        }
  }
} //foreach($getBalances as $index)
$gateBalance = $totalBalance;
$gateBalance = format_balance($gateBalance);

$kevlar['ADA_amt'] = 131000;
$kevlar['ADA_bal'] = $kevlar['ADA_amt'] * $bidKCS['ADA_USDT'];
$kevlar['total_bal'] += $kevlar['ADA_bal'];
$kevlar['ADA_bal'] = format_balance($kevlar['ADA_bal']);

//22000 23000
$ironborn['ADA_amt'] = $totalUSD / $bidKCS['ADA_USDT']; //get from bittrex api
$ironborn['ADA_bal'] = $ironborn['ADA_amt'] * $bidKCS['ADA_USDT'];
$ironborn['ADA_bal'] = format_balance($ironborn['ADA_bal']);

//VET vs KCS
$kevlar['VET_amt'] = 418101.7669886;
$kevlar['VET_bal'] = $kevlar['VET_amt'] * $bidKCS['VET_USDT'];
$kevlar['total_bal'] += $kevlar['VET_bal'];
$kevlar['VET_bal'] = format_balance($kevlar['VET_bal']);

$ironborn1['KCS_amt'] = $KC1Balance; //KC1 balance
$ironborn1['KCS_bal'] = $ironborn1['KCS_amt'] * $bidKCS['KCS_USDT'];

$ironborn2['ADA_amt'] = $KC2Balance; //KC2 balance
$ironborn2['ADA_bal'] = $ironborn2['ADA_amt'] * $bidKCS['ADA_USDT'];

$ironborn3['KCS_amt'] = $KC3Balance; //KC3 balance
$ironborn3['KCS_bal'] = $ironborn3['KCS_amt'] * $bidKCS['KCS_USDT'];

$ironbornT['KCS_amt'] = $ironborn1['KCS_amt'] + $ironborn3['KCS_amt']; //T balance
$ironbornT['KCS_bal'] = $ironborn1['KCS_bal'] + $ironborn3['KCS_bal'];


//format balances
$ironborn1['KCS_bal'] = format_balance($ironborn1['KCS_bal']);
$ironborn2['ADA_bal'] = format_balance($ironborn2['ADA_bal']);
$ironborn3['KCS_bal'] = format_balance($ironborn3['KCS_bal']);
$ironbornT['KCS_bal'] = format_balance($ironbornT['KCS_bal']);


$kevlar['DOGE_amt'] = 93117; 
$kevlar['DOGE_bal'] = $kevlar['DOGE_amt'] * $bidKCS['DOGE_USDT'];
$kevlar['total_bal'] += $kevlar['DOGE_bal'];
$kevlar['DOGE_bal'] = format_balance($kevlar['DOGE_bal']);

$kevlar['BTC_amt'] = 1; 
$kevlar['BTC_bal'] = $kevlar['BTC_amt'] * $bidKCS['BTC_USDT'];
$kevlar['total_bal'] += $kevlar['BTC_bal'];
$kevlar['BTC_bal'] = format_balance($kevlar['BTC_bal']);

$ironborn['Pionex_bal'] = 2600;
$ironborn['Pionex_bal'] = format_balance($ironborn['Pionex_bal']);

$kevlar['total_bal'] = format_balance($kevlar['total_bal']);

?>
<h2>Compete <img src="include/images/refresh.png" class="clickable" onclick="javascript:competeModule()" width="25px" /></h2>

<br /><br />

<div class="row">
    <div class="col-sm-5">
		<table class="table">
		<thead class="thead-default">
		<tr>
			<td></td>
			<td>Kevlar</td>
			<td>Ironborn </td>
		</tr>
		<tr>
			<td>ADA</td>
			<td><?=$kevlar['ADA_amt'] ?> </td>
			<td><?=$ironborn['ADA_amt'] ?>  </td>
		</tr>
		<tr>
			<td><?=$bidKCS['ADA_USDT'] ?></td>
			<td><?=$kevlar['ADA_bal'] ?> </td>
			<td><?=$ironborn['ADA_bal'] ?>  </td>
		</tr>
		<tr>
			<td><br /></td>
		</tr>
		<tr>
			<td></td>
			<td>VET</td>
		</tr>
		<tr>
			<td>VET</td>
			<td><?=$kevlar['VET_amt'] ?> </td>
		</tr>
		<tr>
			<td><?=$bidKCS['VET_USDT'] ?></td>
			<td><?=$kevlar['VET_bal'] ?>  </td>
			<td> </td>
		</tr>
		<tr>
			<td><br /></td>
		</tr>
		<tr>
			<td></td>
			<td>Total</td>
			<td>KC1</td>
			<td>KC3</td>
		</tr>
		<tr>
			<td>KCS</td>
			<td><?=$ironbornT['KCS_amt'] ?> </td>
			<td><?=$ironborn1['KCS_amt'] ?> </td>
			<td><?=$ironborn3['KCS_amt'] ?></td>
			<td></td>
		</tr>
		<tr>
			<td><?=$bidKCS['KCS_USDT'] ?> </td>
			<td><?=$ironbornT['KCS_bal'] ?> </td>
			<td><?=$ironborn1['KCS_bal'] ?> </td> 
			<td><?=$ironborn3['KCS_bal'] ?> </td> 
		</tr>
		<tr>
			<td><br /></td>
		</tr>
		<tr>
			<td>Robinhood Doge</td>
			<td><?=$kevlar['DOGE_amt'] ?></td>
			<td>Gate.io Balance</td>
			<td><?=$gateBalance ?></td>	 
		</tr>
			<td><?=$bidKCS['DOGE_USDT'] ?></td>
			<td><?=$kevlar['DOGE_bal'] ?></td>
		</tr>
		<tr>
			<td><br /></td>
		</tr>
		<tr>
			<td>BTC</td>
			<td>1</td>
			<td>Old Man</td>
			<td>30000</td>	
			<td>Pionex</td>
			<td>2500</td>	  
		</tr>
		<tr>
			<td><?=$bidKCS['BTC_USDT'] ?></td>
			<td><?=$kevlar['BTC_bal'] ?></td>
		</tr>
		<tr>
			<td><br /></td>
		</tr>
		<tr>
			<td>Total</td>
			<td><?=$kevlar['total_bal'] ?></td>
		</tr>
		</table>
    </div>
</div>