<?php
	
	session_start();
	
	
	require __DIR__.'/library/bittrex/Client.php';
	
	$_SESSION['key'] = '6ebadd9db0504586bf7f26a5adae9ccf';
	$_SESSION['secret'] = 'f04b39348a884448837641d0c2504588';

	if(!empty($_SESSION['key']) && !empty($_SESSION['secret'])){  
		try{
			$_SESSION['bittrex'] = new Client ($_SESSION['key'], $_SESSION['secret']);
		echo ',,,';	
		}catch(Exception $e){
			echo 'bad';
			//header('Location:index.php');
		}
	}else{
		
		//header('Location:index.php');
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
      <li class="active"><a href="account.php">Account</a></li>
      <li ><a href="check_price.php">Check Price</a></li>
      <li ><a href="trade.php">Trade</a></li>
	  <li ><a href="operation.php?task=logout">Logout</a></li>
    </ul>
  </div>
</nav>
  
<div class="container">
  <h3>Account Information</h3>
  <p>
			      <?php 
				 echo $_SESSION['bittrex']->getBalance('btc');
				  
			      	$balance = $_SESSION['bittrex']->getBalances();
			      	if(!empty($balance)){ ?>
			      		<table>
			      		<?php foreach($balance as $x => $y){?>
			      			<tr><td>Currency : </td><td><?php echo $y->Currency; ?></td></tr>
			      			<tr><td>Balance : </td><td><?php echo $y->Balance; ?></td></tr>
			      			<tr><td>Pending : </td><td><?php echo $y->Pending; ?></td></tr>
			      			<tr><td>CryptoAddress : </td><td><?php echo $y->CryptoAddress; ?></td></tr>
			      			<tr><td>Uuid : </td><td><?php echo $y->Uuid; ?></td></tr>
			      			<tr><td colspan="2"><hr/></td></tr>
			      		<?php }?>
			      		</table>
			      	<?php } ?></p>
</div>

	</body>
	<link rel="stylesheet" type="text/css" href="assets/css/style.css">
	<script type="text/javascript" src="assets/js/script.js"></script>    
</html>
