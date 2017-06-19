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


//alert ajax calls
$createAlert = 'include/ajax.php?action=create4';
$readAlert = 'include/ajax.php?action=read';
$updateAlert = 'include/ajax.php?action=update';
$deleteAlert = 'include/ajax.php?action=delete';

//trade Ajax calls
$createTrade = 'include/ajax.php?action=createTrade';
$readTrade = 'include/ajax.php?action=readTrade';
$updateTrade = 'include/ajax.php?action=updateTrade';
$deleteTrade = 'include/ajax.php?action=deleteTrade';

global $db;

$db = new ezSQL_mysql($dbUser, $dbPW, $dbName, $dbHost);


//requires the extension php_openssl to work
$polo = new poloniex($polo_api_key, $polo_api_secret);

$btce = new BTCeAPI();

$tableData = new Database($db);

$condTable = $tableData->alertsTable();

$tradesTable = $tableData->tradesTable();


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
?>
<table class="table">
	<thead class="thead-default">
		<th>Currency Pair</th>
		<th>Poloniex Price</th>
		<th>BTCe Price</th>
	</thead>
	<tr>
		<td>BTC/USDT </td>
		<td> $<?=$polo_btc_usd ?> </td>
		<td> $<?=$btce_btc_usd ?> </td>
	</tr>
	</tr>
		<td>ETH/USDT </td>
		<td> $<?=$polo_eth_usd ?> </td>
		<td> $<?=$btce_eth_usd ?> </td>
	</tr>
	<tr>
		<td>DASH/USDT </td>
		<td> $<?=$polo_dash_usd ?> </td>
		<td> $<?=$btce_dash_usd ?> </td>
	</tr>
	<tr>
		<td>LTC/USDT </td>
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
					<th colspan="5">btc_alerts Table <a href="cronSendAlerts.php?debug=1" target="_BLANK"><input type="button" value="cronSendAlerts"></a>
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
					<th colspan="6">btc_trades Table <a href="cronSendTrades.php?debug=1" target="_BLANK"><input type="button" value="cronSendTrades"></a>
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
<table class="table">
		<thead class="thead-default">
			<tr>
				<th colspan="3">Today's Winners
				<a href="cronAutoTrade.php?debug=1" target="_BLANK"><input type="button" value="cronAutoTrade"></th>
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
			echo '<tr>
			<td>'.$currencyPair.'</td>
			<td class="green">+'.$percentChangeFormat.'%</td>
			<td>'.$tickerData['last'].'</td></tr>';
		}
	}
	?>
	</table>
	</div>
	

<?
	
}
else if($_GET['page'] == 'balanceTable'){

?>


	<table class="table">
		<thead class="thead-default">
			<tr>
				<th colspan="5">Polo Balanace</th>
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
		
			foreach($tickerArray as $currencyPair => $tickerData) {
				list($crap, $curr) = explode('_',  $currencyPair);
			
				if($currency == $curr && $crap == 'BTC') {
					$percentChange = $tickerData['percentChange'];
					$lastFormat = $tickerData['last'];
					
					$percentChangeFormat = $percentChange * 100;
					$percentChangeFormat = number_format($percentChangeFormat, 2);
					
					$lastFormat = number_format($lastFormat, 8);
					
					//$openOrders = $polo->get_open_orders($currencyPair);

					//echo $currencyPair;
					//print_r($openOrders);
				}	
			}

			
			if($currency == 'BTC') {
				$chartLink = 'BTCUSD';
				$percentChangeFormat = $lastFormat = 0;
				$btcValue = $currencyBalance;
			}
			else { 
				$chartLink = $currency.'BTC';
				$btcValue = $lastFormat * $currencyBalance;
			}
			
			$totalBTC += $btcValue;
			
			
			if($percentChangeFormat > 0) $color = 'green';
			else $color = 'red';
			
			$balanceTable .= '<tr><td>'.$currency.'</td>
			<td>'.$currencyBalance.'</td>
			<td>'.$lastFormat.'</td>
			<td style="color: '.$color.'">'.$percentChangeFormat.'%</td>
			<td>'.$btcValue.'</td>
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
		