<?php


function transfer($r1_btc){
	
global $polo_btc, $polo_eth, $coinbase_btc, $coinbase_eth;

$fee = 0.0025; //0.25% fee
	
	echo 'Polo BTC: '.$r1_btc.'<br />';
	
	$r1_usdt = $polo_btc * $r1_btc;
	$r1_usdt = $r1_usdt - ($r1_usdt * $fee);
	
	echo 'Polo USDT: '.$r1_usdt.'<br />';
	
	$r1_coin = $r1_usdt / $polo_eth;
	$r1_coin = $r1_coin - ($r1_coin * $fee);
	
	echo 'Polo ETH: '.$r1_coin.'<br />';
	echo '<br />Transfer to CB...<br />';
	echo 'CB ETH: '.$r1_coin.'<br />';
	
	
	
	$r2_usdt = $r1_coin * $coinbase_eth;
	$r2_usdt = $r2_usdt - ($r2_usdt * $fee);
	
	echo 'CB USDT: '.$r2_usdt.'<br />';
	
	$r2_btc = $r2_usdt / $coinbase_btc;
	$r2_btc = $r2_btc - ($r2_btc * $fee);
	
	
	echo 'CB BTC: '.$r2_btc.'<br />'; 
	
	return $r2_btc;
}

global $polo_btc, $polo_eth, $coinbase_btc, $coinbase_eth;

$polo_btc = 2265;
$polo_eth = 181;

$coinbase_btc = 2317;
$coinbase_eth = 185;

if($_POST['balance']) {
	
	$bal = $_POST['balance'];
	
	echo 'Start balance: '.$bal.'<br /><br />';
	
	$r1_btc = $bal / $polo_btc;
	
	echo 'Round 1<br />';
	$round1_btc = transfer($r1_btc);
	
	echo '<br /><br />Round 2<br />';
	
	transfer($round1_btc);
}
?>
<form method=post>
<input type="text" name="balance">USD<input type=submit>
</form>

Polo <br />
BTC - <?=$polo_btc?> <br />
ETH - <?=$polo_eth?> <br />

<br /><br />
Coinbase <br />
BTC - <?=$coinbase_btc?> <br />
ETH - <?=$coinbase_eth?> <br />
<br /><br />