<?php
$dir = 'include/';
include($dir.'functions.php');
include($dir.'config.php');
include($dir.'api_database.php');
include($dir.'api_bittrex.php');
include($dir.'api_kucoin.php');

//set timezone
date_default_timezone_set('America/New_York');

if($_GET['accessKey'] != 'AllLivesMatter') {
	echo "Wrong access key"; exit;
}
	
$bittrex = new Client ($bittrex_api_key, $bittrex_api_secret);


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

	//Raw prices
	$polo_btc_usd_raw = $polo_btc_usd_ticker['last'];
	$polo_eth_usd_raw = $polo_eth_usd_ticker['last'];
	$polo_ltc_usd_raw = $polo_ltc_usd_ticker['last'];

	$polo_xrp_usd_raw = $polo_xrp_usd_ticker['last'];

	$polo_bchabc_usd_raw = $polo_bchabc_usd_ticker['last'];

	//format polo currencies
	$polo_btc_usd = number_format($polo_btc_usd_raw, 0);

	$polo_eth_usd = number_format($polo_eth_usd_raw, 2);

	$polo_ltc_usd = number_format($polo_ltc_usd_raw, 2);

	$polo_xrp_usd = number_format($polo_xrp_usd_raw, 2);
	
	$polo_bchabc_usd = $polo_bchabc_usd_raw; //number_format($polo_bsv_usd_raw, 2);

	
	//format polo percentChanges
	$btc_percent_raw = $polo_btc_usd_ticker['percentChange'] * 100;
	$eth_percent_raw = $polo_eth_usd_ticker['percentChange'] * 100;
	$ltc_percent_raw = $polo_ltc_usd_ticker['percentChange'] * 100;
	$xrp_percent_raw = $polo_xrp_usd_ticker['percentChange'] * 100;
	$bchabc_percent_raw = $polo_bchabc_usd_ticker['percentChange'] * 100;
	
	// $btc_percent_display = $tableData->format_percent_display($btc_percent_raw);
	// $eth_percent_display = $tableData->format_percent_display($eth_percent_raw);
	// $ltc_percent_display = $tableData->format_percent_display($ltc_percent_raw);
	// $xrp_percent_display = $tableData->format_percent_display($xrp_percent_raw);
	// $bchabc_percent_display = $tableData->format_percent_display($bchabc_percent_raw);
	
	/*
	===================
	Bittrex prices
	===================
	*/
	//get currency format for Bittrex
	$bittrex_btc_usd_raw = $bittrex->getTicker('USDT-BTC')->Last;
	$bittrex_eth_usd_raw = $bittrex->getTicker('USDT-ETH')->Last;
	$bittrex_ltc_usd_raw = $bittrex->getTicker('USDT-LTC')->Last;
	$bittrex_xrp_usd_raw = $bittrex->getTicker('USDT-XRP')->Last;
	$bittrex_bchabc_usd_raw = $bittrex->getTicker('USDT-BCH')->Last;
	
	
	
	$bittrex_btc_usd = number_format($bittrex_btc_usd_raw, 2);
	$bittrex_eth_usd = number_format($bittrex_eth_usd_raw, 2);
	$bittrex_ltc_usd = number_format($bittrex_ltc_usd_raw, 2);
	$bittrex_xrp_usd = number_format($bittrex_xrp_usd_raw, 2);
	$bittrex_bchabc_usd = number_format($bittrex_bchabc_usd_raw, 2);
	
	
	/*
	===================
	Coinbase prices
	===================
	*/
	// $coinbase_btc_usd = $tableData->coinbasePrice('btc-usd');
	// $coinbase_eth_usd = $tableData->coinbasePrice('eth-usd');
	// $coinbase_ltc_usd = $tableData->coinbasePrice('ltc-usd');
	// $coinbase_bch_usd = $tableData->coinbasePrice('bch-usd');
	// $coinbase_xrp_usd = $tableData->coinbasePrice('xrp-usd');
	
	$coinbase_btc_usd = number_format($coinbase_btc_usd, 2);
	$coinbase_eth_usd = number_format($coinbase_eth_usd, 2);
	$coinbase_ltc_usd = number_format($coinbase_ltc_usd, 2);
	$coinbase_bch_usd = number_format($coinbase_bch_usd, 2);
	$coinbase_xrp_usd = number_format($coinbase_xrp_usd, 2);
	
	
if($_GET['page'] == 'priceTable') {
		
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
			<th>Bittrex</th>
			<th>Coinbase</th>
		</tr>
		</thead>
		<tr>
			<td><a href="https://www.tradingview.com/chart/BTCUSD" target="_BLANK">BTC/USDT</a></td><td><?=$btc_percent_display?></td>
			<td> $<?=$polo_btc_usd ?></td>
			<td> $<?=$bittrex_btc_usd?> </td>
			<td> $<?=$coinbase_btc_usd ?></td>
		</tr>
		</tr>
			<td><a href="https://www.tradingview.com/chart/ETHUSD" target="_BLANK">ETH/USDT</a></td><td><?=$eth_percent_display?></td>
			<td> $<?=$polo_eth_usd ?> </td>
			<td> $<?=$bittrex_eth_usd?> </td>
			<td> $<?=$coinbase_eth_usd ?></td>
		</tr>
		<tr>
			<td><a href="https://www.tradingview.com/chart/LTCUSD" target="_BLANK">LTC/USDT</a></td><td> <?=$ltc_percent_display?></td>
			<td> $<?=$polo_ltc_usd ?> </td>
			<td> $<?=$bittrex_ltc_usd?> </td>
			<td> $<?=$coinbase_ltc_usd ?></td>
		</tr>		
		<tr>
			<td><a href="https://www.tradingview.com/chart/BCHUSD" target="_BLANK">BCH/BTC or BCH/USDT</a></td><td> <?=$bchabc_percent_display?></td>
			<td> <?=$polo_bchabc_usd ?> </td>
			<td> $<?=$bittrex_bchabc_usd?> </td>
			<td> $<?=$coinbase_bch_usd?> </td>
		</tr>
		<tr>
			<td><a href="https://www.tradingview.com/chart/XRPUSD" target="_BLANK">XRP/USDT</a></td><td> <?=$xrp_percent_display?></td>
			<td> $<?=$polo_xrp_usd ?> </td>
			<td> $<?=$bittrex_xrp_usd?> </td>
			<td> $<?=$coinbase_xrp_usd?> </td>
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
else if($_GET['page'] == 'btrexBalance') {
		
	try {
		$balance = $bittrex->getBalances();
	}
	catch(Exception $e){
		echo 'Error occurred : '.$e->getMessage(); 
	}	
	
		?>
		<table class="table">
			<thead class="thead-default">
			<tr>
				<th colspan="8">Bittrex Balance <img src="include/refresh.png" class="clickable" onclick="javascript:reloadBtrexBalance()" width="25px" /> </th>
			</tr>
			<tr>
				<th>Currency</th><th>Balance</th><th>Price</th><th>BTC Value</th><th>USDT</th>
			</tr>
		<?
		
		if(!empty($balance)) { 
			foreach($balance as $key => $val) {
			
				$currency = $val->Currency; //coin 
				$currencyBalance = $val->Balance; //coin balance
				
				if($currency == 'BTXCRD') continue; //invalid market
				if($currency == 'BTC' ) {
					$currencyPair = 'USDT-BTC';
					$lastFormat = $bittrex_btc_usd_raw; //formatted price
					$btcValue = $currencyBalance; //btc value
					$usdtValue = $btcValue * $bittrex_btc_usd_raw; //get usdt value
				}
				else if ($currency == 'USDT') {
					$lastFormat = 1; //usdt has no price
					$usdtValue = $currencyBalance; 
					$btcValue = $currencyBalance / $bittrex_btc_usd_raw;
				}
				else { //all other coins
					$currencyPair = 'BTC-'.$currency; //ticker pair
					$ticker = $bittrex->getTicker($currencyPair);

					$lastFormat = $ticker->Last;
					$btcValue = $currencyBalance * $lastFormat;	//btc value
					$usdtValue = $btcValue * $bittrex_btc_usd_raw; //get usdt value
				}
			
				$btcValueFormat = number_format($btcValue, 4);
				$usdtValueFormat = number_format($usdtValue, 2);
				$totalBTC += $btcValue;
				$totalUSD += $usdtValue;
				
				//echo $currency.' '.$currencyBalance.' '.$usdtValue.' '.$ticker->Last.' '.$bittrex_btc_usd_raw.' <br />';
				
				if($btcValue > 0.01 || $usdtValue > 1) {
					echo '<tr><td>'.$currency.'</td>
					<td>'.$currencyBalance.'</td>
					<td>'.$lastFormat.'</td>
					<td>'.$btcValueFormat.'</td>
					<td><span style="color: gray">'.$usdtValueFormat.'</span></td>
					</tr>';
	
				} //if($btcValue > 0.01 || $usdtValue > 1)
			} //foreach($balance as $x => $val)
			 
			$totalBTCFormat	= number_format($totalBTC, 4);
			$totalUSDTFormat = number_format($totalUSD, 2);
			 
			echo '<tr><td colspan="10">Total BTC: '.$totalBTCFormat.' &nbsp;&nbsp; 
			<span style="color: gray">Total USDT: $'.$totalUSDTFormat.'</span></td>';
		
			echo '</table>';
		} //if(!empty($balance)) 	
}
else if($_GET['page'] == 'bitmexPositions') {
	
	$bitmex = new bitmex($bitmex_api_key, $bitmex_api_secret);
	
	$bitmexPos = $bitmex->getOpenPositions();
	$getWallet = $bitmex->getWallet();
	$totalSatoshiBalance = $totalSatoshiAvailable = $getWallet['amount'];
	?>
	<table class="table">
		<thead class="thead-default">
			<tr>
				<th colspan="8">Bitmex Positions <img src="include/refresh.png" class="clickable" onclick="javascript:reloadBitmexPositions1()" width="25px" /> </th>
			</tr>
			<tr>
				<th>Symbol</th><th>openingQty</th><th>markPrice</th><th>avgEntryPrice</th><th>breakEvenPrice</th><th>unrealisedGrossPnl</th><th>liquidationPrice</th>
			</tr>
			
		</thead>
		<tbody>
		
		<?
		//markPrice unrealisedGrossPnl
		foreach($bitmexPos as $num => $pos) {
			
			$unrealizedPNL = $pos['unrealisedGrossPnl'];
			
			$totalSatoshiAvailable += $unrealizedPNL;
			
			$unrealizedPNL = $unrealizedPNL/100000000;
			
			$unrealizedPNLDisplay = $tableData->format_change_display($unrealizedPNL, 4);
			
			$openingQty = $tableData->format_change_display($pos['openingQty'], 2);
			
			
			echo '<tr><td>'.$pos['symbol'].'</td>
			<td>'.$openingQty.'</td>
			<td>'.$pos['markPrice'].'</td>
			<td>'.$pos['avgEntryPrice'].'</td>
			<td>'.$pos['breakEvenPrice'].'</td>
			<td>'.$unrealizedPNLDisplay.'</td>
			<td>'.$pos['liquidationPrice'].'</td>
			</tr>';
		}
		
	$totalXBTBalance = $totalSatoshiBalance/100000000; 
	$totalXBTAvailable = $totalSatoshiAvailable/100000000;
	
	echo '<tr><td colspan="3">BTC '.$totalXBTBalance.'  
	Available BTC '.$totalXBTAvailable.'</td></tr>';
	
	?>
		</tbody>
	</table>
	
<?
}




?>