<?php
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'api_poloniex.php');
include($dir.'config.php');
include($dir.'ez_sql_core.php');
include($dir.'ez_sql_mysql.php');


//connect to Poloniex
$polo = new poloniex($polo_api_key, $polo_api_secret);

	
set_time_limit ( 100 );




if($_POST['sub']) {

	$pair = $_POST['pair'];

	$action = $_POST['sub'];

	$tradeAmount = $_POST['tradeAmount'];
	
	list($coin, $market) = explode('/', $pair); // XXX/BTC
	$currencyPair = $market.'_'.$coin; //currency format is BTC_XXX

	$priceArray = $polo->get_ticker($currencyPair); 

	$lastPrice = $priceArray['last']; //most recent price for this coin

	
	
	if($action == 'Buy All') {
		
		while (true) {
	
			$order_book = $polo->get_order_book($currencyPair);
	
			echo $lastAskPrice = $order_book['asks'][0][0];
	
			$tradeResult = $polo->buy($currencyPair, $lastAskPrice, $tradeAmount, 'immediateOrCancel'); 

			echo '<pre>'.print_r($tradeResult).'</pre>';

			
			if($tradeResult['amountUnfilled'] == 0) break;
		}
	}
	else {
	
		while (true) {
			
			$order_book = $polo->get_order_book($currencyPair);
	
			
			echo $lastBidPrice = $order_book['bids'][0][0];
			
			$tradeResult = $polo->sell($currencyPair, $lastBidPrice, $tradeAmount, 'immediateOrCancel'); 

			echo '<pre>'.print_r($tradeResult).'</pre>';
			
			if($tradeResult['amountUnfilled'] == 0) break;
		}
	}
}

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
</head>

<table>
	<tr valign="top">
		<td>
		<form method="POST">
		
		currencyPair <input type="text" name="pair" value=""><br />
		tradeAmount <input type="text" name="tradeAmount"><br />
		
		<br />
		<input type="submit" name="sub" class="btn btn-success" value="Buy All">
		</form>

		<br /><br />
		Asks<br />
		<?php
		 
		foreach($order_book['asks'] as $key => $arr) {
			echo 'price: '.$arr[0].' amt: '.$arr[1] .' <br />'; 
		}
		
		?>
	</td>
	<td>
		<form method="POST">
		currencyPair <input type="text" name="pair" value="<?=$_POST['pair']?>"><br />
		tradeAmount <input type="text" name="tradeAmount"><br />
	
		<br />
		<input type="submit" name="sub" class="btn btn-danger" value="Sell All">
		</form>
		
		<br /><br />
		Bids<br />
		<?php
		
//echo		$lastBidPrice = $order_book['bids'][0][0];

		foreach($order_book['bids'] as $key => $arr) {
			echo 'price: '.$arr[0].' amt: '.$arr[1] .' <br />'; 
		}
		
		?>
	</td>
	</tr>
	</table>
</form>