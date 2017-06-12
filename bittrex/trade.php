<?php
	session_start();
	require __DIR__.'/library/bittrex/Client.php';

	if(!empty($_SESSION['key']) && !empty($_SESSION['secret'])){  
		try{
			$_SESSION['bittrex'] = new Client ($_SESSION['key'], $_SESSION['secret']);
		}catch(Exception $e){
			header('Location:index.php');
		}
	}else{
		header('Location:index.php');
	}


?>


<!DOCTYPE html>
<html lang="en">
    <head>

		<title>Bittrex Api</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
    	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
		    <!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

		<!-- Optional theme -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

		<!-- Latest compiled and minified JavaScript -->
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular.min.js"></script>
        

    </head>
    <body>
   
	<nav class="navbar navbar-default">
	  <div class="container-fluid">
	    <div class="navbar-header">
	      <a class="navbar-brand" href="#">Bittrex</a>
	    </div>
	    <ul class="nav navbar-nav">
	      <li ><a href="account.php">Account</a></li>
	      <li ><a href="check_price.php">Check Price</a></li>
	      <li class="active"><a href="trade.php">Trade</a></li>
		  <li ><a href="operation.php?task=logout">Logout</a></li>
	    </ul>
	  </div>
	</nav>
	  
	<div class="container">
	  <h3>Trade</h3>
	  <form action="#" method="post" name="getTicker" class="">
	   	  <div class="form-group">
		    <label for="method">Market : </label>
		    <select id="method" name="method" class="form-control">
		    	<option value="">Select</option>
		    	<option value="Buy">Buy</option>
		    	<option value="Sell">Sell</option>
		    	<option value="Cancel">Cancel</option>
		    </select>
		  </div>
		  <div class="otherBox">
		  <div class="form-group">
		    <label for="market">Market : </label>
		    <input type="text" id="market" class="form-control" name="market" placeholder="Market (for ex: BTC-LTC)"  />
		  </div>

		  <div class="form-group">
		    <label for="quantity">Quantity:</label>
		    <input type="text" class="form-control" id="quantity" name="quantity" placeholder="Quantity" />     		  
		  </div>


		  <div class="form-group">
		    <label for="rate">Rate:</label>
		    <input type="text" class="form-control" id="rate" name="rate" placeholder="Rate" />     		  
		  </div>
		  </div>

		  <div class="cancelBox">
			  <div class="form-group">
			    <label for="uuid">UUID (Id of buy or sell order):</label>
			    <input type="text" class="form-control" id="uuid" name="uuid" placeholder="UUID of buy or sell order" />  
			  </div>
		  </div>
		 

		  <input type="submit" name="submit" value="Submit" class="btn btn-default" onclick="return confirm('You are about to place an order');"/>
		  <input type="hidden" name="task" value="getTicker"/>

		</form>

		<h3>Result : </h3>
		<hr/>
	  	<p><?php 

      	if(!empty($_POST['market']) && !empty($_POST['quantity']) && !empty($_POST['rate']) && isset($_POST['submit']) && isset($_POST['method'])) { 

      		$market 	= htmlspecialchars(trim($_POST['market']));
      		$quantity 	= floatval($_POST['quantity']);
      		$rate 		= floatval($_POST['rate']);
      		$method 	= htmlspecialchars(trim($_POST['method']));
      		$uuid 		= isset($_POST['uuid'])?htmlspecialchars(trim($_POST['uuid'])):'';

      		switch ($method) {
      			case 'Buy':
      				$method = "sellLimit";
      				break;
      			case 'Sell':
      				$method = "buyLimit";
      				break;
      			case 'Cancel':
      				$method = "cancel";
      				break;      			
      			default:
      				$method = "Undefined method";
      				break;
      		}

      		try{

      			if(isset($method) && $method !== 'cancel')
      				$result = $_SESSION['bittrex']->$method($market, $quantity, $rate);	
      			else
      				$result = $_SESSION['bittrex']->cancel($uuid);	

      			echo 'Result : <pre>';print_R($result); echo '</pre>';
	      		?>
		      	<?php 
      		}catch(Exception $e){
      			echo 'Error occurred : '.$e->getMessage(); 
      		}

      	}?>      		

      	</p>
	</div>

	</body>

	<link rel="stylesheet" type="text/css" href="assets/css/style.css">
	<script type="text/javascript" src="assets/js/script.js"></script>    
</html>




