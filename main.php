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


$alertDeleteDiv = '<button id="deleteAlert" class="btn btn-danger">Delete</button>';
?>

<head>
	<title>BTC API Dashboard</title>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">

	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">

	<!-- JQueryUI -->
	<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css" />

	<script src="//code.jquery.com/jquery-latest.min.js" type='text/javascript' /></script>

	<script src="include/jquery-ui/ui/jquery-ui.js"></script>
	
	<link rel="shortcut icon" type="image/png" href="include/dollar_sign.png">

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
	
	h3 {
		margin-bottom: 20px;
	}
	
	div {
		display: block /*for the img inside your div */  
		margin-left: auto ;
		margin-right: auto ;
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
	
	<button class = "coinValue btn btn-success">CoinValue</button>
	
	<a href="script_send_trades.php?debug=1" target="_BLANK"><input type="button" value="cronSendTrades"></a>
	
	<a href="script_send_alerts.php?debug=1" target="_BLANK"><input type="button" value="cronSendAlerts"></a>
	
	<a href="buy_sell_polo.php?accessKey=<?=$accessKey?>" target="_BLANK"><input type="button" value="Trade Polo"></a>
	
	<a href="buy_sell_bittrex.php?accessKey=<?=$accessKey?>" target="_BLANK"><input type="button" value="Trade Bittrex"></a>
	
	<br /><br />
	
	<div class="row">
		<div class="col-6">
			<div id="counter"></div>
			
			<div id="priceTable"></div>
	
			<div id="cronSendAlerts"></div>
	
			<div id="btcTrades"></div>
			
			<br />
		
		</div>
    
		<div class="col">
		
			<div id="notesDiv"></div>
			
			<br />
		
			<div id="links_to_charts">
				<h3>Links to Charts</h3>
				
				<table>
					<tr valign="top">
						<td>
							<a href="https://coinmarketcap.com/" target="_blank">Coin Market Cap</a> - 
							
							
							<a href="https://coinmarketcap.com/" target="_blank">7 day price</a> - 
							
							<a href="http://coinmarketcap.com/currencies/views/all" target="_blank">1h 24h 7d</a>
							
							<br /><br />
							
							<a href="https://coinfarm.online/index.asp" target="_blank">Coinfarm Margin Trading</a>
							
						
							<br /><br /><br />
							
							<h3>Links to TV</h3>
							
							<a href="https://www.tradingview.com/chart/KUndzcja/" target="_blank">XBTUSD</a> -
							<a href="https://www.tradingview.com/chart/TfA11XY2/" target="_blank">ETHUSD</a> - 
							<a href="https://www.tradingview.com/chart/gghdcAkO/" target="_blank">XRP M19</a> - 
							
							<a href="https://www.tradingview.com/chart/HRbO8WTw/" target="_blank">LTC M19</a> - 
						 
							<a href="https://www.tradingview.com/chart/cva6RNPy/" target="_blank">XXX BTC</a>

							<br /><br />
							
							<a href="https://www.tradingview.com/chart/BTCUSD" target="_blank">TradingView Analysis</a> - 
							
							<a href="https://www.tradingview.com/chart/BTCUSD" target="_blank">BTC/USD</a> - 
							
							<a href="https://www.tradingview.com/chart/ETHUSD" target="_blank">ETH/USD</a>
							
							
							<br /><br />
							
							
							<a href="https://www.tradingview.com/chart/pcgYAYg9/" target="_blank">Gold</a> - 
							
							<a href="https://www.tradingview.com/chart/F1C8WVYs/" target="_blank">Platinum</a> - 
							
							<a href="https://www.tradingview.com/chart/vqNVwtnT/" target="_blank">Palladium</a>
							
							
							<br /><br /><br />
					</tr>
				</table>
				
            </div>
			
			<div id="coinbase_links">
				<h3>Exchange Links</h3>
				
				<table>
					<tr valign="top">
						<td>
							
							<a href="https://www.coinbase.com/accounts" target="_blank">Coinbase Funds</a> - <a href="https://www.gdax.com/" target="_blank">GDAX Exchange</a>
							
							<br /><br />
							
							<a href="https://poloniex.com/login" target="_blank">Polo Login</a> - 
							
							<a href="https://bittrex.com/account/login" target="_blank">Bittrex Login</a> - 
							
							
							<a href="https://www.binance.com/login.html" target="_blank">Binance Login</a> 
							
							<br /><br />
							
							 
							<p>&nbsp;</p>						
						</td>				   
					</tr>
				</table>
			</div>
			
		</div>
	</div>

	<div class="row">
	
		<div class="col-md-12">
		<center>
		<img src="https://alternative.me/crypto/fear-and-greed-index.png" alt="Latest Crypto Fear & Greed Index" width="500px" /></center>
		</div>
	
		<br />
		<div class="col">
			<div id="cronAutoTrade"></div>
		</div>
		
		<div class="col-md-12">
			<div id="balanceTable"></div>
		</div>

		
		<br />
		<div class="col-md-12">
			<div id="btrexBalance"> </div>
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
			<div id="alertDeleteDiv">
				<button id="deleteAlert" class="btn btn-danger">Delete</button>
			</div>
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