<?php


include('include/api_database.php');
include('include/api_poloniex.php');
include('include/config.php');

//$candleData = new Database($db);
//$candleData->sendMail();


$polo = new poloniex();


$BTC_ETH = $polo->get_ticker('BTC_ETH');

$USDT_BTC = $polo->get_ticker('USDT_BTC');

$USDT_ETH = $polo->get_ticker('USDT_ETH');



?>

<pre class="xdebug-var-dump">
<table>
<tr>
	<td>ETH/BTC </td>
	<td><?=$BTC_ETH['last']; ?>
</td>
</tr>
	<td>BTC/USDT </td>
	<td> <?=$USDT_BTC['last'] ?> </td>
</tr>
</tr>
	<td>ETH/USDT </td>
	<td> <?=$USDT_ETH['last']?> </td>
</tr>
</table>
</pre>


<?
exit;

include('index1.html');
?>