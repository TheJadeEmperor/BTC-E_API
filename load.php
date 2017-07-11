<?php
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'api_poloniex.php');
include($dir.'api_btce.php');
include($dir.'config.php');
include($dir.'ez_sql_core.php');
include($dir.'ez_sql_mysql.php');

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

//set timezone
date_default_timezone_set('America/New_York');


if($_GET['key'] != 'YoMamaSoFat') exit;


global $db;

$db = new ezSQL_mysql($dbUser, $dbPW, $dbName, $dbHost);


//requires the extension php_openssl to work
$polo = new poloniex($polo_api_key, $polo_api_secret);

$btce = new BTCeAPI();

$tableData = new Database($db);

$condTable = $tableData->alertsTable();

$tradesTable = $tableData->tradesTable();

function format_percent_display($percent_number) {
	$percent_number = number_format($percent_number, 2).'%';
	
	if($percent_number > 0) {
		$percent_number = '<span class="green">+'.$percent_number.'</span>';
	} 
	else{
		$percent_number = '<span class="red">'.$percent_number.'</span>';		
	}
	
	return $percent_number;
}




$conditionDropDown = '<select name="on_condition"><option value=">"> > </option><option value="<"> < </option></select>';

$tradeConditionDropDown = '<select name="trade_condition"><option value=">"> > </option><option value="<"> < </option></select>';



$unitTypes = array(
	'BTC',
	'%',
);

foreach($unitTypes as $uType) {
	$unitDropDown .= '<option value="'.$uType.'">'.$uType.'</option>';
}
$tradeUnitDropDown = '<select name="trade_unit">'.$unitDropDown.'</option>';



$exchangeTypes = array(
	'Poloniex'
);

foreach($exchangeTypes as $eType) {
	$exchangeDropDown .= '<option value="'.$eType.'">'.$eType.'</option>';
	$tradeExchangeDropDown .= '<option value="'.$eType.'">'.$eType.'</option>';
}
$exchangeDropDown = '<select name="exchange">'.$exchangeDropDown.'</option>';
$tradeExchangeDropDown = '<select name="trade_exchange">'.$tradeExchangeDropDown.'</option>';
 


$sentTypes = array(
	'No', 'Yes',
);

foreach($sentTypes as $sType) {
	$sentDropDown .= '<option value="'.$sType.'">'.$sType.'</option>';
}
$sentDropDown = '<select name="sent">'.$sentDropDown.'</option>';
 

$actionTypes = array(
	'Buy', 'Sell'
); 

foreach($actionTypes as $aType) {
	$actionDropDown .= '<option value="'.$aType.'">'.$aType.'</option>'; 
}
$tradeActionDropDown = '<select name="trade_action">'.$actionDropDown.'</option>';


if($_GET['page'] == 'priceTable'){
	
//get prices from btc-e
$btce_dash_usd = $btce->getLastPrice('dsh_usd');

$btce_btc_usd = $btce->getLastPrice('btc_usd');

$btce_eth_usd = $btce->getLastPrice('eth_usd');

$btce_ltc_usd = $btce->getLastPrice('ltc_usd');

//format btc-e currencies
$btce_dash_usd =  number_format($btce_dash_usd, 2);

$btce_btc_usd = number_format($btce_btc_usd, 0);

$btce_eth_usd = number_format($btce_eth_usd, 2);

$btce_ltc_usd =  number_format($btce_ltc_usd, 2);

//get prices from poloniex
$POLO_USDT_DASH = $polo->get_ticker('USDT_DASH');

$POLO_USDT_BTC = $polo->get_ticker('USDT_BTC');

$POLO_USDT_ETH = $polo->get_ticker('USDT_ETH');

$POLO_USDT_LTC = $polo->get_ticker('USDT_LTC');


//format polo currencies
$polo_dash_usd = number_format($POLO_USDT_DASH['last'], 2);

$polo_btc_usd = number_format($POLO_USDT_BTC['last'], 0);

$polo_eth_usd = number_format($POLO_USDT_ETH['last'], 2);

$polo_ltc_usd = number_format($POLO_USDT_LTC['last'], 2);


//format polo percentChanges
$dash_percent_raw = $POLO_USDT_DASH['percentChange'] * 100;
$btc_percent_raw = $POLO_USDT_BTC['percentChange'] * 100;
$eth_percent_raw = $POLO_USDT_ETH['percentChange'] * 100;
$ltc_percent_raw = $POLO_USDT_LTC['percentChange'] * 100;

$dash_percent_display = format_percent_display($dash_percent_raw);
$btc_percent_display = format_percent_display($btc_percent_raw);
$eth_percent_display = format_percent_display($eth_percent_raw);
$ltc_percent_display = format_percent_display($ltc_percent_raw);



$bittrexURL = 'http://bestpayingsites.com/admin/btcTradingAPI/bittrex/';
?>

<script>
 $(document).ready(function () {
	 $.ajax({

		// The 'type' property sets the HTTP method.
		// A value of 'PUT' or 'DELETE' will trigger a preflight request.
		type: 'GET',

		// The URL to make the request to.
		url: '<?=$bittrexURL?>?curr=btc',


		contentType: 'text/plain',

		

		success: function(data) {
			alert(data);
			$( "#usdt_btc_last" ).html( data );
		},
	  
		 //$( "#usdt_btc_last" ).html( '<?=$bittrexURL?>?curr=btc' );
	 });
	});
</script>

<table class="table">
	<thead class="thead-default">
	<tr>
		<th colspan="5">Price Table <img src="include/refresh.png" class="clickable" onclick="javascript:reloadPriceTable()" width="25px" /></th>
	</tr>
	<tr>
		<th>Currency Pair</th>
		<th>Percent Change</th>
		<th>Poloniex Price</th>
		<th>BTCe Price</th>
	</tr>
	</thead>
	<tr>
		<td>BTC/USDT</td><td><?=$btc_percent_display?></td>
		<td> $<?=$polo_btc_usd ?> </td>
		<td> $<?=$btce_btc_usd ?> </td>
		<td></td>
	</tr>
	</tr>
		<td>ETH/USDT</td><td><?=$eth_percent_display?></td>
		<td> $<?=$polo_eth_usd ?> </td>
		<td> $<?=$btce_eth_usd ?> </td>
	</tr>
	<tr>
		<td>DASH/USDT</td><td> <?=$dash_percent_display?></td>
		<td> $<?=$polo_dash_usd ?> </td>
		<td> $<?=$btce_dash_usd ?> </td>
	</tr>
	<tr>
		<td>LTC/USDT</td><td> <?=$ltc_percent_display?></td>
		<td> $<?=$polo_ltc_usd ?> </td>
		<td> $<?=$btce_ltc_usd ?> </td>
	</tr>				
</table>
<?
}
else if($_GET['page'] == 'cronSendAlerts') {

?>
	<table class="table">
		<thead class="thead-default">
		<tr>
			<th colspan="5">btc_alerts Table <img src="include/refresh.png" class="clickable" onclick="javascript:reloadAlertTable()" width="25px" />
			</th>
		</tr>
		<tr>
			<th>Currency</th>
			<th>Condition</th>
			<th>Price</th>
			<th>Exchange</th>
			<th>Sent?</th>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach($condTable as $cond) {
			echo '<tr class="clickable" onclick="javascript:updateAlertDialog(\''.$cond->id.'\')" title="'.$cond->id.'">
			<td>'.$cond->currency.'</td>
			<td>'.$cond->on_condition.'</td>
			<td>'.$cond->price.' '.$cond->unit.'</td>
			<td>'.$cond->exchange.'</td>
			<td>'.$cond->sent.'</td>
		';
		}

		?>
		</tbody>
	</table>
			
<?
}
else if($_GET['page'] == 'btcTrades'){
?>
	<table class="table">
		<thead class="thead-default">
		<tr>
			<th colspan="6">btc_trades Table 
			<img src="include/refresh.png" class="clickable" onclick="javascript:reloadTradesTable()" width="25px" />
			</th>
		</tr>
		<tr>
			<th>Currency</th>
			<th>Condition</th>
			<th>Amount</th>
			<th>Valid Until</th>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach($tradesTable as $trade) {
			$trade_amount = number_format($trade->trade_amount, 4);
			
			if($trade->result==1) {
				$style="background-color: gray";
			}
			else{
				$style="background-color: white";
			}
			echo '<tr style="'.$style.'" class="clickable" onclick="javascript:updateTradeDialog(\''.$trade->id.'\')" title="id: '.$trade->id.' | trade_until: '.$trade->until_date.' '.$trade->until_time.'">
			<td>'.$trade->trade_currency.'</td>
			<td>'.$trade->trade_condition.' 
			'.$trade->trade_price.' '.$trade->trade_unit.'</td>
			<td>'.$trade->trade_action.' '.$trade_amount.'</td>
			<td>'.$trade->until_format.'</td>
		';
		}

		?>
		</tbody>
		</tr>
	</table>
<?
}
else if($_GET['page'] == 'cronAutoTrade'){
?>

<div class="row">
	<div class="col">
	<table class="table">
		<thead class="thead-default">
			<tr>
				<th colspan="3">Today's Winners <img src="include/refresh.png" class="clickable" onclick="javascript:cronAutoTrade()" width="25px" />
				</th>
			</tr>
			<tr>
				<th>Currency</th>
				<th>Percent Change</th>
				<th>Last Price</th>
			</tr>
		</thead>
	<?php
	$tickerArray = $polo->get_ticker();
	foreach($tickerArray as $currencyPair => $tickerData) {
		$percentChange = $tickerData['percentChange'];
		
		list($crap, $curr) = explode('_',  $currencyPair);
		
		$percentChangeFormat = $percentChange * 100;
		
		$percentChangeFormat = number_format($percentChangeFormat, 2);
		
		if($crap == 'BTC') //only show BTC markets
		if($percentChangeFormat > 10) {
			
			if($percentChangeFormat > 15) {
				$percentChangeFormat = '<b>'.$percentChangeFormat.'</b>';
			}
			echo '<tr>
			<td>'.$currencyPair.'</td>
			<td class="green">+'.$percentChangeFormat.'%</td>
			<td>'.$tickerData['last'].'</td></tr>';
		}
	}
	?>
	</table>
	</div>
	
	<div class="col">
	<table class="table">
		<thead class="thead-default">
			<tr>
				<th colspan="3">Today's Losers <img src="include/refresh.png" class="clickable" onclick="javascript:cronAutoTrade()" width="25px" />
				</th>
			</tr>
			<tr>
				<th>Currency</th>
				<th>Percent Change</th>
				<th>Last Price</th>
			</tr>
		</thead>
	<?php
	$tickerArray = $polo->get_ticker();
	foreach($tickerArray as $currencyPair => $tickerData) {
		$percentChange = $tickerData['percentChange'];
		
		list($crap, $curr) = explode('_',  $currencyPair);
		
		$percentChangeFormat = $percentChange * 100;
		
		$percentChangeFormat = number_format($percentChangeFormat, 2);
		
		if($crap == 'BTC') //only show BTC markets
		if($percentChangeFormat < -10) {
			
			if($percentChangeFormat < -16) {
				$percentChangeFormat = '<b>'.$percentChangeFormat.'</b>';
			}
			echo '<tr>
			<td>'.$currencyPair.'</td>
			<td class="red">'.$percentChangeFormat.'%</td>
			<td>'.$tickerData['last'].'</td></tr>';
		}
	}
	?>
	</table>
	</div>
	
</div>
	
<?
	
}
else if($_GET['page'] == 'balanceTable'){

?>


	<table class="table">
		<thead class="thead-default">
			<tr>
				<th colspan="6">Polo Balance <img src="include/refresh.png" class="clickable" onclick="javascript:reloadBalanceTable()" width="25px" /> </th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<th>Currency</th><th>Balance</th><th>Price</th><th>Change</th><th>BTC Value</th><th>Chart</th>
			</tr>
	<?php
	$balanceArray = $polo->get_balances();

	$tickerArray = $polo->get_ticker();
			
	foreach($balanceArray as $currency => $currencyBalance) {
		if($currencyBalance > 0.5) {
		
			if($currency == 'BTC') {
				$chartLink = 'BTCUSD';
				$currencyPair = 'USDT_BTC';
				$lastFormat = $tickerArray[$currencyPair]['last'];
				$lastFormat = '$'.number_format($lastFormat, 2);
				$btcValue = $currencyBalance;		
			}
			else { 
				$chartLink = $currency.'BTC';
				$currencyPair = 'BTC_'.$currency;
				$lastFormat = $tickerArray[$currencyPair]['last'];
				$lastFormat = number_format($lastFormat, 8);
				$btcValue = $lastFormat * $currencyBalance;
			}
			
			$percentChange = $tickerArray[$currencyPair]['percentChange'];
			$percentChangeFormat = $percentChange * 100;
			$percentChangeFormat = number_format($percentChangeFormat, 2);
			
			$btcValueFormat = number_format($btcValue, 4);
			$totalBTC += $btcValue;
			
			if($percentChangeFormat > 0) $color = 'green';
			else $color = 'red';
			
			if($currency == 'BTC' || $currency == 'ETH')
				$formatting = 'style="font-weight: bold;"';
			else
				$formatting = 'style="font-weight: normal;"';
			
			$balanceTable .= '<tr '.$formatting.'><td>'.$currency.'</td>
			<td>'.$currencyBalance.'</td>
			<td>'.$lastFormat.'</td>
			<td style="color: '.$color.'">'.$percentChangeFormat.'%</td>
			<td>'.$btcValueFormat.'</td>
			<td><a href="https://www.tradingview.com/chart/'.$chartLink.'" target="_BLANK">View</a></td>
			</tr>';
			
			
		}
	}
	echo $balanceTable;
	
	?>
	</tbody>
</table>
Total BTC: <?=$totalBTC?>
<?
}
?>