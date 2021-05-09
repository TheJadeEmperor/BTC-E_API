<?
$dir = '../include/';
include($dir.'api_database.php');
include($dir.'api_gate.php');
include($dir.'functions.php');
include($dir.'config.php');

$Gate = new Gate($gate_key, $gate_secret);

$getBalances = $Gate->getBalances();

$subAccount = 'gateBalance';
$url = 'https://www.gate.io/myaccount';

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

        if ($USDTBalance > 1) //do not show small balances
        $output .= '<tr><td><a href="https://www.gate.io/en/trade/'.$currency.'_USDT">'.$currency.'</a></td><td>'.$available.'</td><td>'.$bid.'</td><td>'.$USDTBalance.'</td></tr>';
  }
} //foreach($getBalances as $index)

$output .= '<tr><td>Total: </td><td>'.$totalBalance.'</td></tr>';

echo $output;

?>