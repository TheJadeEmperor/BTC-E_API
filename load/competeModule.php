<?php
$dir = '../include/';
include($dir.'api_database.php');
include($dir.'api_gate.php');
include($dir.'api_kucoin.php');
include($dir.'api_bittrex.php');
include($dir.'functions.php');
include($dir.'config.php');


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

$KC1Balance = getKCSubBalance($KC1, 'VET');
$KC2Balance = getKCSubBalance($KC2, 'VET');
$KC3Balance = getKCSubBalance($KC3, 'KCS');


$currencyPairs = array(
	'ADA_USDT', 'VET_USDT', 'KEY_USDT', 'DOGE_USDT', 'BTC_USDT'
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
		else if ($currency == 'ADA') {
			$bittrexAdaAmt = $currencyBalance;
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


$kevlar['ADA_amt'] = 131000;
$kevlar['ADA_bal'] = $kevlar['ADA_amt'] * $bidKCS['ADA_USDT'];


//16000 15505 
$ironborn['ADA_amt'] = $totalUSD / $bidKCS['ADA_USDT']; //get from bittrex api
$ironborn['ADA_bal'] = $ironborn['ADA_amt'] * $bidKCS['ADA_USDT'];

$kevlar['VET_amt'] = 140627;
$kevlar['VET_bal'] = $kevlar['VET_amt'] * $bidKCS['VET_USDT'];

$ironborn1['VET_amt'] = $KC1Balance; //KC1 balance
$ironborn1['VET_bal'] = $ironborn1['VET_amt'] * $bidKCS['VET_USDT'];

$ironborn2['VET_amt'] = $KC2Balance; //KC2 balance
$ironborn2['VET_bal'] = $ironborn2['VET_amt'] * $bidKCS['VET_USDT'];

$ironbornT['VET_amt'] = $ironborn1['VET_amt'] + $ironborn2['VET_amt'];
$ironbornT['VET_bal'] = $ironborn1['VET_bal'] + $ironborn2['VET_bal'];

$kevlar['KEY_amt'] = 2889871;
$kevlar['KEY_bal'] = $kevlar['KEY_amt'] * $bidKCS['KEY_USDT'];

$ironborn['KCS_amt'] = $KC3Balance; //kc3
$ironborn['KCS_bal'] = $ironborn['KCS_amt'] * $bidKCS['KCS_USDT'];

$kevlar['DOGE_amt'] = 87708; 
$kevlar['DOGE_bal'] = $kevlar['DOGE_amt'] * $bidKCS['DOGE_USDT'];


$kevlar['BTC_amt'] = 1;
$kevlar['BTC_bal'] = $kevlar['BTC_amt'] * $bidKCS['BTC_USDT'];

$ironborn['Pionex_bal'] = 2600;

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
			<td>Total</td><td>Total</td>
			<td>VET-1</td>
			<td>VET-2</td>
		</tr>
		<tr>
			<td>VET</td>
			<td><?=$kevlar['VET_amt'] ?> </td>
			<td><?=$ironbornT['VET_amt'] ?>  </td>
			<td><?=$ironborn1['VET_amt'] ?>  </td>
			<td><?=$ironborn2['VET_amt'] ?>  </td>
		</tr>
		<tr>
			<td><?=$bidKCS['VET_USDT'] ?></td>
			<td><?=$kevlar['VET_bal'] ?>  </td>
			<td><?=$ironbornT['VET_bal'] ?>  </td>
			<td><?=$ironborn1['VET_bal'] ?>  </td>
			<td><?=$ironborn2['VET_bal'] ?>  </td>
		</tr>
		<tr>
			<td><br /></td>
		</tr>
		<tr>
			<td>Key</td>
			<td><?=$kevlar['KEY_amt'] ?>  </td>
			<td>KCS</td>
			<td><?=$ironborn['KCS_amt'] ?>  </td>	 
		</tr>
		<tr>
			<td><?=$bidKCS['KEY_USDT'] ?> </td>
			<td><?=$kevlar['KEY_bal'] ?> </td>
			<td><?=$bidKCS['KCS_USDT'] ?> </td>
			<td><?=$ironborn['KCS_bal'] ?> </td> 	
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
			<td>40000</td>	
			<td>Pionex</td>
			<td>2400</td>	  
		</tr>
		<tr>
			<td><?=$bidKCS['BTC_USDT'] ?></td>
			<td><?=$kevlar['BTC_bal'] ?></td>
		</tr>
		</table>
    </div>
</div>