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
	      <li class="active"><a href="check_price.php">Check Price</a></li>
	      <li ><a href="trade.php">Trade</a></li>
	      <li ><a href="operation.php?task=logout">Logout</a></li>
	    </ul>
	  </div>
	</nav>
	  
	<div class="container">
	  <h3>Account</h3>



	  	<form action="#" method="post" name="getTicker" class="">
		  <div class="form-group">
		    <label for="market">Market : </label>
		    <input type="text" id="market" class="form-control" name="market" placeholder="Market (for ex: BTC-LTC)" required="" />
		  </div>
		  <input type="submit" name="submit" value="Submit" class="btn btn-default"/>
		  <input type="hidden" name="task" value="getTicker"/>
		</form>

		<h3>Result : </h3>
		<hr/>
	  	<p><?php 

      	if(!empty($_POST['market']) && isset($_POST['submit'])) { 
      		try{
      			$ticker = $_SESSION['bittrex']->getTicker($_POST['market']);	

	      		?>
		  		<table>
		  			<tr><td class="half">Bid Price : </td><td class="half"><?php echo $ticker->Bid; ?></td></tr>
		  			<tr><td class="half">Ask : </td><td class="half"><?php echo $ticker->Ask; ?></td></tr>
		  			<tr><td class="half">Last : </td><td class="half"><?php echo $ticker->Last; ?></td></tr>
		  		</table>
		      	<?php 
      		}catch(Exception $e){
      			echo $e->getMessage(); 
      		}

      	}?>      		

      	</p>
	</div>

	<link rel="stylesheet" type="text/css" href="assets/css/style.css">
	<script type="text/javascript" src="assets/js/script.js"></script>    

	</body>

</html>




