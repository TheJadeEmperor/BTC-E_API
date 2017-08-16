<?php
include('include/config.php');

session_start();

if(!isset($_SESSION['admin']))//if not logged in, redirect back to login page
    header('Location: index.php'); 

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

//set timezone
date_default_timezone_set('America/New_York');



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


$unitTypes = array(
	'BTC',
	'$',
);

foreach($unitTypes as $uType) {
	$alertUnitDropDown .= '<option value="'.$uType.'">'.$uType.'</option>';
}

$unitDropDown = '<select name="unit">'.$alertUnitDropDown.'</option>';



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

?>

<head>
	<title>BTC API Dashboard</title>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">

	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">

	<!-- JQueryUI -->
	<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css" />

	<script src="http://code.jquery.com/jquery-latest.min.js" type='text/javascript' /></script>

	<script src="include/jquery-ui/ui/jquery-ui.js"></script>

<style>
    body {
        margin: 5px 20px;
    }
	
	.clickable, .btn {
		cursor: pointer;
	}
	
	.green {
		color: green;
	}
	
	.red {
		color: red;
	}
</style>

<?
include('scripts.html');
?>

</head>
<body>
<div class="container">

 	<button class = "createButton btn btn-primary">Add Alert</button>
	
	<button class = "tradeButton btn btn-success">Add Trade</button>
	
	<a href="cronSendTrades.php?debug=1" target="_BLANK"><input type="button" value="cronSendTrades"></a>
	
	<a href="cronSendAlerts.php?debug=1" target="_BLANK"><input type="button" value="cronSendAlerts"></a>
	
	<a href="buy_sell_all.php?accessKey=<?=$accessKey?>" target="_BLANK"><input type="button" value="Buy & Sell"></a>
	
	<br /><br />
	
	
	<div class="row">
		<div class="col-6">
		 
			<div id="priceTable"></div>
	
			<div id="cronSendAlerts"></div>
			
			<br />
		
		</div>
    
		<div class="col">
		
			<div id="notesDiv"></div>
			
			<br />
		
			<div id="links_to_charts">
				<h3>Links to Charts</h3>
				<pre class="xdebug-var-dump">
				<table>
					<tr valign="top">
						<td>
							<a href="http://coinmarketcap.com/currencies/views/all" target="_blank">Coin Market Cap</a>
							<br /><br />
							
							<a href="https://www.tradingview.com/chart/BTCUSD" target="_blank">TradingView BTC/USD</a>
							<br /><br />
							
							<a href="https://www.tradingview.com/chart/ETHUSD" target="_blank">TradingView ETH/USD</a>
							<br /><br />
							
							<a href="http://bitcoinwisdom.com/markets/bitfinex/btcusd" target="_blank">Bitcoin Wisdom</a>
							
						</td>
						<td width="18px"></td>
					</tr>
					</table>
				</pre>
            </div>
			
			<div id="coinbase_links">
				<h3>Coinbase Links</h3>
				<pre class="xdebug-var-dump">
				<table class="">
					<tr valign="top">
						<td>

							<a href="https://www.coinbase.com/accounts" target="_blank">Coinbase Funds</a>
							
							<br /><br />
							
							<a href="https://www.gdax.com/" target="_blank">GDAX Exchange</a>
							
						</td>
					</tr>
				</table>
				</pre>
			</div>
			
			<div id="poloniex_links">
				<h3>Poloniex Links</h3>
				<pre class="xdebug-var-dump">
				<table>
					<tr valign="top">
						<td>
							<a href="https://poloniex.com/login" target="_blank">Polo Login</a>
							
							<br /><br />
							
							<a href="https://poloniex.com/exchange" target="_blank">Polo Exchange</a>
							
							<br /><br />
							
							<a href="https://poloniex.com/balances" target="_blank">Polo Funds</a>
							
							<br /><br />
							
							<a href="https://bittrex.com/account/login" target="_blank">Bittrex Exchange</a>
											
						</td>
					   
					</tr>
				</table>
				</pre>
			</div>
			
		</div>
	</div>

	<div class="row">
		<div class="col-6">
			<div id="btcTrades"></div>
		</div>
		<div class="col-md-5">
			<div id="balanceTable"></div>
		</div>

		<div class="col">
			<div id="cronAutoTrade"></div>
		</div>

	</div>
</div><!--container-->
  
<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>

<form id="conditionTable" title="Price Alerts">
    
	<input type="hidden" id="id" name="id" />
	
	<table class="table">
	<thead class="thead-default">
	<tr>
		<th>Currency</th>
		<th>Condition</th>
		<th>Price</th>
		<th>Unit</th>
		<th>Exchange</th>
	</tr>
	</thead>
	<tr>
		<td>
			<input type="text" name="currency" size="10" />
		
		</td>
		<td>
			<div id="conditionDiv">
			<?=$conditionDropDown?>
			</div>
		</td>
		<td>
			<input type="text" name="price" size="15" />
		</td>
		<td>
			<div id="unitDiv"><?=$unitDropDown?></div>
		</td>
		<td>
			<div id="exchangeDiv"><?=$exchangeDropDown?></div>
		</td>
		
	</tr>
	</table>
	<table>
	<tr>
		<td width="200px">
			Already Sent? <span id="sentDiv"><?=$sentDropDown?></span> 
		</td>
		<td>
			<button id="deleteAlert" class="btn btn-danger">Delete</button>

		</td>
	</tr>
	</table>	
</form>


<form id="tradeTable" title="Active Trades">

	<input type="hidden" id="trade_id" name="trade_id" />

	<table class="table">
	<thead class="thead-default">
	<tr>
		<th>Exchange</th>
		<th>Currency</th>
		<th>Condition</th>
		<th>Price </th>		
		<th>Unit</th>
	</tr>
	</thead>
	<tr>
		<td>
			<div id="tradeExchangeDiv"><?=$tradeExchangeDropDown?></div>
		</td>
		<td>
			<input type="text" name="trade_currency" size="10" />
		</td>
		<td>
			<div id="tradeConditionDiv"><?=$tradeConditionDropDown?></div>
		</td>
		<td>
			<input type="text" name="trade_price" size="10" />	
		</td>
		<td>
			<div id="tradeUnitDiv"><?=$tradeUnitDropDown?></div>
		</td>
	</tr>
	</table>
	
	<table class="table">
	<thead class="thead-default">
	<tr>
		<th>Action</th>
		<th>Amount</th>
		<th>Date Time</th>
		<th>Delete</th>
	</tr>
	</thead>
	<tr>
		<td>
			<div id="tradeActionDiv">
				<?=$tradeActionDropDown?>
			</div>
		</td>
		<td>
			<input type="text" name="trade_amount" size="12" />
		</td>
		<td>
			<input type="text" name="until_date" id="until_date" size="10" />
			<input type="text" name="until_time" id="until_time" size="10" />
		</td>	
		<td>
			<div id="deleteTradeButtonDiv">
			<button id="deleteTrade" class="btn btn-danger">Delete</button>
			</div>
			
		</td>
	</tr>
	</table>
</form>


</body>