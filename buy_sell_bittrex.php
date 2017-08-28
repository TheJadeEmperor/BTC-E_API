<?php
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'api_bittrex.php');
include($dir.'config.php');
include($dir.'ez_sql_core.php');
include($dir.'ez_sql_mysql.php');


//connect to Bittrex
$bittrex = new Client ($bittrex_api_key, $bittrex_api_secret);
	

set_time_limit ( 100 );




if($_POST['sub']) {

	$pair = $_POST['pair'];

	$action = $_POST['sub'];

	$tradeAmount = $_POST['tradeAmount'];
	
	list($coin, $market) = explode('/', $pair); // XXX/BTC
	$currencyPair = $market.'_'.$coin; //currency format is BTC_XXX
	
	//get currency format for Bittrex

	$priceArray = $polo->get_ticker($currencyPair); 

	$lastPrice = $priceArray['last']; //most recent price for this coin

	
	if($action == 'Buy All') {
		
		//$tradeResult = buyMarket($pair, $tradeAmount);
		
		echo '<pre>'.print_r($tradeResult).'</pre>';
		
	 
	}
	else {
	
		//$tradeResult = sellMarket ($pair, $tradeAmount);
		
		echo '<pre>'.print_r($tradeResult).'</pre>';
	
		
	}
}

$loadBalanceTable = 'load.php?page=balanceTable&accessKey='.$accessKey;
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
	
	<script>
		$(document).ready(function () {
			
			//reloadBalanceTable();
			
		});	

		function reloadBalanceTable(){
			//$('#balanceTable').load('<?=$loadBalanceTable?>');		
		}
			
	
	</script>
</head>



<div class="container">
<div class="row"> 
<div class="col-md-5">
	<div id="balanceTable"></div>
</div>
</div>

<div class="row"> 
<div class="col">
	<form method="POST">
		
		<table>
			<tr>
				<td>currencyPair</td>
				<td> <input type="text" name="pair" value=""></td>
			</tr>
			<tr>
				<td>tradeAmount</td>
				<td> <input type="text" name="tradeAmount"></td>
			</tr>
		</table>
		
		
		<br />
		<input type="submit" name="sub" class="btn btn-success" value="Buy All" onclick="return confirm('You are about to place an order!')" />
		</form>

		<br /><br />
		Asks<br />
		<?php
		if(is_array($order_book))
		foreach($order_book['asks'] as $key => $arr) {
			echo 'price: '.$arr[0].' amt: '.$arr[1] .' <br />'; 
		}
		
		?>
</div>
<div class="col">
	<form method="POST">
		
		<table>
			<tr>
				<td>currencyPair </td>
				<td><input type="text" name="pair" value="<?=$_POST['pair']?>"></td>
			</tr>
			<tr>
				<td>tradeAmount</td>
				<td> <input type="text" name="tradeAmount"></td>
			</tr>
		</table>
			
		<br />
		<input type="submit" name="sub" class="btn btn-danger" value="Sell All" onclick="return confirm('You are about to place an order!')" />
		</form>
		
		<br /><br />
		Bids<br />
		<?php
	
		if(is_array($order_book))
		foreach($order_book['bids'] as $key => $arr) {
			echo 'price: '.$arr[0].' amt: '.$arr[1] .' <br />'; 
		}
		
		?>
</div>


</div>
</div> 
</div>