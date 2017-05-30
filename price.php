<?php
function transfer($r1_btc){
	
$fee = 0.0025; //0.25% fee
$polo_btc = 2055;
$polo_ltc = 23.99;

$cb_btc = 2236;
$cb_ltc = 24.82;
	
	echo 'Polo BTC: '.$r1_btc.'<br />';
	
	$r1_usdt = $polo_btc * $r1_btc;
	$r1_usdt = $r1_usdt - ($r1_usdt * $fee);
	
	echo 'Polo USDT: '.$r1_usdt.'<br />';
	
	$r1_dash = $r1_usdt / $polo_ltc;
	$r1_dash = $r1_dash - ($r1_dash * $fee);
	
	echo 'Polo DASH: '.$r1_dash.'<br />';

	echo '<br />Transfer to Coinbase...<br />';

	echo 'CB LTC: '.$r1_dash.'<br />';
	
	$r2_usdt = $r1_dash * $cb_ltc;
	$r2_usdt = $r2_usdt - ($r2_usdt * $fee);
	
	echo 'CB USDT: '.$r2_usdt.'<br />';
	
	$r2_btc = $r2_usdt / $cb_btc;
	$r2_btc = $r2_btc - ($r2_btc * $fee);
	
	
	echo 'CB BTC: '.$r2_btc.'<br />'; 
	
	return $r2_btc;
}

$polo_btc = 2055;
$polo_ltc = 23.99;

$cb_btc = 2236;
$cb_ltc = 24.82;

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
BTC - 1369 <br />
DASH - 78 <br />

<br /><br />
Cryptopia <br />
BTC - 1356 <br />
DASH - 84 <br />
<br /><br />
