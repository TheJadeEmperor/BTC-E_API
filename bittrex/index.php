<?php
include('../include/config.php');
include('api_bittrex.php');

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

session_start();
	 
	
$_SESSION['key'] = '6ebadd9db0504586bf7f26a5adae9ccf';
$_SESSION['secret'] = 'f04b39348a884448837641d0c2504588';

	 
$bittrex = new Client ($_SESSION['key'], $_SESSION['secret']);

		
$usdt_btc_price = $bittrex->getTicker('USDT-BTC');
$usdt_btc_last = $usdt_btc_price->Last;

	
$usdt_eth_price = $bittrex->getTicker('USDT-ETH');
$usdt_eth_last = $usdt_eth_price->Last;


$btc_ltc_price = $bittrex->getTicker('BTC-LTC');
$btc_ltc_last = $btc_ltc_price->Last;


$usdt_ltc_last = $btc_ltc_last * $usdt_btc_last;


$btc_dash_price = $bittrex->getTicker('BTC-DASH');
$btc_dash_last = $btc_dash_price->Last;

$usdt_dash_last = $btc_dash_last * $usdt_btc_last;

//echo '<br>';


if($_GET['curr'] == 'btc') {
	echo $usdt_btc_last;
}
else if($_GET['curr'] == 'eth') {
	echo $usdt_eth_last;	
}
else if($_GET['curr'] == 'ltc') {
	echo $usdt_ltc_last;
}
else if($_GET['curr'] == 'dash') {
	echo $usdt_dash_last;
}
else {
	 
//print_r($bittrex->getBalance('btc'));
  
  
	$balance = $bittrex->getBalances();

	print_r($balance);

}
//echo '<br>';

//echo '<br>';




//echo $btc_ltc_last;

//echo '<br>';



	?>