<?php
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'api_poloniex.php');
include($dir.'config.php');
include($dir.'ez_sql_core.php');
include($dir.'ez_sql_mysql.php');


error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

//set timezone
date_default_timezone_set('America/New_York');

if($_GET['accessKey'] != 'KickInTheDick') exit;


global $db;

$db = new ezSQL_mysql($dbUser, $dbPW, $dbName, $dbHost);


//requires the extension php_openssl to work
$polo = new poloniex($polo_api_key, $polo_api_secret);

//$btce = new BTCeAPI();

$tableData = new Database($db);

$condTable = $tableData->alertsTable();

$tradesTable = $tableData->tradesTable();

$optionsTable = $tableData->getSettingsFromDB();

//print_r($condTable);

foreach($optionsTable as $option) {
	if($option->opt == 'notes') 
		$notes = $option->setting;
}


if($_GET['page'] == 'notes') {

	$loadNotesAjax = 'include/ajax.php?action=updateNotes';
	$loadNotes = 'load.php?page=notes&accessKey='.$accessKey;
	?>
	<script>
	function updateNotes() {
		$.ajax({ //Process the form using $.ajax()
			type        : 'POST', //Method type
			url         : '<?=$loadNotesAjax?>', //Your form processing file url
			data        : $('#notesForm').serialize(), 
			success     : function(msg) {
				console.log( msg );
				alert(msg);
				reloadNotes();
			}
		});
		event.preventDefault(); //Prevent the default submit      
	}
	
	$(document).ready(function () {
		$( "input[name=updateNotes]" ).click(function() {
			updateNotes();
		});
	});
	</script>

	<div id="notesArea">
		<form id="notesForm">
		<textarea rows="7" cols="50" name="notes"><?=$notes?></textarea><br />
		<input type="button" class="btn btn-success" name="updateNotes" value="Submit">
		</form>
	</div>
	<?
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


	/*
	===================
	Poloniex prices
	===================
	*/
	$polo_btc_usd_ticker = $polo->get_ticker('USDT_BTC');

	$polo_eth_usd_ticker = $polo->get_ticker('USDT_ETH');

	$polo_ltc_usd_ticker = $polo->get_ticker('USDT_LTC');

	$polo_dash_usd_ticker = $polo->get_ticker('USDT_DASH');
	
	//Raw prices
	
	$polo_btc_usd_raw = $polo_btc_usd_ticker['last'];
	
	$polo_eth_usd_raw = $polo_eth_usd_ticker['last'];
	
	$polo_ltc_usd_raw = $polo_ltc_usd_ticker['last'];

	$polo_dash_usd_raw = $polo_dash_usd_ticker['last'];

	//format polo currencies

	$polo_btc_usd = number_format($polo_btc_usd_raw, 0);

	$polo_eth_usd = number_format($polo_eth_usd_raw, 2);

	$polo_ltc_usd = number_format($polo_ltc_usd_raw, 2);

	$polo_dash_usd = number_format($polo_dash_usd_raw, 2);

	
	//format polo percentChanges
	$btc_percent_raw = $polo_btc_usd_ticker['percentChange'] * 100;
	$eth_percent_raw = $polo_eth_usd_ticker['percentChange'] * 100;
	$ltc_percent_raw = $polo_ltc_usd_ticker['percentChange'] * 100;
	$dash_percent_raw = $polo_dash_usd_ticker['percentChange'] * 100;

	$btc_percent_display = $tableData->format_percent_display($btc_percent_raw);
	$eth_percent_display = $tableData->format_percent_display($eth_percent_raw);
	$ltc_percent_display = $tableData->format_percent_display($ltc_percent_raw);
	$dash_percent_display = $tableData->format_percent_display($dash_percent_raw);

	
	$coinbase_btc_usd = $tableData->coinbasePrice('btc-usd');
	$coinbase_eth_usd = $tableData->coinbasePrice('eth-usd');
	$coinbase_ltc_usd = $tableData->coinbasePrice('ltc-usd');

	
	if($_GET['page'] == 'arb') {
		
		
		
function transfer($bal) {
	
	global $exchange1, $exchange2;

	$ex1_c1_amt = $bal / $exchange1['coin1_price'];
	$ex1_c1_amt = $ex1_c1_amt - ($ex_c1_amt * $exchange1['fee']);
	
	echo $exchange1['name'].' USDT: '.$bal.' <br />'.
	$exchange1['name'].' '.$exchange1['coin1_name'].': '.$ex1_c1_amt.'<br />';

	
	$ex2_usd_amt = $ex1_c1_amt * $exchange2['coin1_price'];
	$ex2_usd_amt = $ex2_usd_amt - ($ex2_usd_amt * $exchange2['fee']);
	
	$ex2_c2_amt = $ex2_usd_amt / $exchange2['coin2_price'];
	$ex2_c2_amt = $ex2_c2_amt - ($ex2_c2_amt * $exchange2['fee']);
	
	echo '<br />Transfer to '.$exchange2['name'].'...<br />';
	
	echo $exchange2['name'].' '.$exchange2['coin1_name'].' '.$ex1_c1_amt.'<br />';
	echo $exchange2['name'].' USDT: '.$ex2_usd_amt.'<br />';
	echo $exchange2['name'].' '.$exchange2['coin2_name'].' '.$ex2_c2_amt.'<br />';
	
	
	$ex1_usd_amt = $ex2_c2_amt * $exchange2['coin2_price'];
	$ex1_usd_amt = $ex1_usd_amt - ($ex1_usd_amt * $exchange2['fee']);
	
	echo '<br />Transfer to '.$exchange1['name'].'...<br />';
	
	echo $exchange1['name'].' '.$exchange1['coin2_name'].': '.$ex2_c2_amt.'<br />
	'.$exchange1['name'].' USDT: '.$ex1_usd_amt.' <br />';

	
	return $ex1_usd_amt;
}


global $exchange1, $exchange2;

$exchange1 = array(
	'name' => 'BTC-E',
	'fee' => 0.0025,
	'coin1_name' => 'BTC',
	'coin1_price' => $btce_btc_usd_raw,
	'coin2_name' => 'LTC',
	'coin2_price' => $coinbase_ltc_usd,
);

$exchange2 = array (
	'name' => 'GDAX',
	'fee' => 0.0025,
	'coin1_name' => 'BTC',
	'coin1_price' => $coinbase_btc_usd,
	'coin2_name' => 'LTC',
	'coin2_price' => $polo_ltc_usd_raw
);
	

if($_POST['balance']) {
	
	$bal = $_POST['balance'];
	
	echo 'Start balance: '.$bal.' USD<br /><br />';
	
	echo 'Round 1<br />';
	
	$bal = transfer($bal);
	
	echo '<br /><br />Round 2<br />';
	
	transfer($bal);
}


?>
<br />
<form method="POST">
	<input type="text" name="balance" value="<?=$_POST['balance']?>">Starting USD
	<input type=submit>
</form>
<?
echo $exchange1['name'].' <br />
'.$exchange1['coin1_name'].' '.$exchange1['coin1_price'].'<br />
'.$exchange1['coin2_name'].' '.$exchange1['coin2_price'].'<br />
<br /><br />'.

$exchange2['name'].' <br />
'.$exchange2['coin1_name'].' '.$exchange2['coin1_price'].'<br />
'.$exchange2['coin2_name'].' '.$exchange2['coin2_price'].'<br />';


	}
	

	
if($_GET['page'] == 'priceTable'){
		
	$bittrexURL = 'http://bestpayingsites.com/admin/btcTradingAPI/bittrex/';
	
	?>

	<table class="table">
		<thead class="thead-default">
		<tr>
			<th colspan="5">Price Table <img src="include/refresh.png" class="clickable" onclick="javascript:reloadPriceTable()" width="25px" /></th>
		</tr>
		<tr>
			<th>Currency Pair</th>
			<th>% Change</th>
			<th>Poloniex</th>
			
			<th>Coinbase</th>
		</tr>
		</thead>
		<tr>
			<td>BTC/USDT</td><td><?=$btc_percent_display?></td>
			<td> $<?=$polo_btc_usd ?> </td>
			
			<td> $<?=$coinbase_btc_usd ?></td>
		</tr>
		</tr>
			<td>ETH/USDT</td><td><?=$eth_percent_display?></td>
			<td> $<?=$polo_eth_usd ?> </td>
			
			<td> $<?=$coinbase_eth_usd ?></td>
		</tr>
		<tr>
			<td>LTC/USDT</td><td> <?=$ltc_percent_display?></td>
			<td> $<?=$polo_ltc_usd ?> </td>
			
			<td> $<?=$coinbase_ltc_usd ?></td>
		</tr>				
		<tr>
			<td>DASH/USDT</td><td> <?=$dash_percent_display?></td>
			<td> $<?=$polo_dash_usd ?> </td>
			
			<td> :*( </td>
		</tr>
	</table>
	<?
}
else if($_GET['page'] == 'cronSendAlerts') {

?>
	<table class="table">
		<thead class="thead-default">
		<tr>
			<th colspan="5">btc_alerts Table <img src="include/refresh.png" class="clickable" onclick="javascript:reloadAlertsTable()" width="25px" />
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
				<th>Currency</th><th>Balance</th><th>Price</th><th>Change</th><th>BTC Value</th><th>USDT</th><th>Chart</th>
			</tr>
	<?php
	$balanceArray = $polo->get_balances();

	$tickerArray = $polo->get_ticker();
			
	foreach($balanceArray as $currency => $currencyBalance) {
		if($currencyBalance > 0.01) {
		
			$btcPrice = $tickerArray['USDT_BTC']['last'];
		
			if($currency == 'BTC') {
				$chartLink = 'BTCUSD';
				$currencyPair = 'USDT_BTC';
				$lastFormat = $tickerArray[$currencyPair]['last'];
				
				$lastFormat = '$'.number_format($lastFormat, 2);
				$btcValue = $currencyBalance;		
				$usdtValue = $btcValue * $btcPrice;	
				
			}
			else if($currency == 'USDT') {
				$chartLink = 'BTCUSD';
				$currencyPair = 'USDT_BTC';
				$lastFormat = $tickerArray[$currencyPair]['last'];
				$btcValue = $currencyBalance / $lastFormat;	
				$usdtValue = $lastFormat;
			}
			else { 
				$chartLink = $currency.'BTC';
				$currencyPair = 'BTC_'.$currency;
				$lastFormat = $tickerArray[$currencyPair]['last'];
				$lastFormat = number_format($lastFormat, 8);
				$btcValue = $lastFormat * $currencyBalance;
				$usdtValue = $btcValue * $btcPrice;
			}
						
			$percentChange = $tickerArray[$currencyPair]['percentChange'];
			$percentChangeFormat = $percentChange * 100;
			$percentChangeFormat = number_format($percentChangeFormat, 2);
			
			$btcValueFormat = number_format($btcValue, 4);
			$totalBTC += $btcValue;
			$usdtValueFormat = number_format($usdtValue, 2);
			
			
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
			<td>'.$usdtValueFormat.'</td>
			<td><a href="https://www.tradingview.com/chart/'.$chartLink.'" target="_BLANK">View</a></td>
			</tr>';
			
		}
	}
	echo $balanceTable;
	
	$totalBTCFormat = number_format($totalBTC, 8);
	
	$totalUSDT = $totalBTC * $btcPrice;
	$totalUSDTFormat = number_format($totalUSDT, 2);
	
	echo '<tr><td colspan="10">Total BTC: '.$totalBTCFormat.' &nbsp;&nbsp; Total USDT: '.$totalUSDTFormat.'</td>';
	
	echo '</tbody>
	</table>';
	

	
	
	//echo $lastFormat;
	
}
?>