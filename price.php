<?php
function transfer($r1_btc){
	
$fee = 0.0025; //0.25% fee
$polo_btc = 1369;
$polo_dash = 78;

$cryp_btc = 1356;
$cryp_dash = 84;
	
	echo 'Polo BTC: '.$r1_btc.'<br />';
	
	$r1_usdt = $polo_btc * $r1_btc;
	$r1_usdt = $r1_usdt - ($r1_usdt * $fee);
	
	echo 'Polo USDT: '.$r1_usdt.'<br />';
	
	$r1_dash = $r1_usdt / $polo_dash;
	$r1_dash = $r1_dash - ($r1_dash * $fee);
	
	echo 'Polo DASH: '.$r1_dash.'<br />';

	echo '<br />Transfer to Cryptopia...<br />';

	echo 'Cryp DASH: '.$r1_dash.'<br />';
	
	$r2_usdt = $r1_dash * $cryp_dash;
	$r2_usdt = $r2_usdt - ($r2_usdt * $fee);
	
	echo 'Cryp USDT: '.$r2_usdt.'<br />';
	
	$r2_btc = $r2_usdt / $cryp_btc;
	$r2_btc = $r2_btc - ($r2_btc * $fee);
	
	
	echo 'Cryp BTC: '.$r2_btc.'<br />'; 
	
	return $r2_btc;
}

$polo_btc = 1369;
$polo_dash = 78;

$cryp_btc = 1356;
$cryp_dash = 84;

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
