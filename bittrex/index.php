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
      <li class="active"><a href="index.php">Login</a></li>
      <li ><a href="account.php">Account</a></li>
      <li ><a href="check_price.php">Check Price</a></li>
      <li ><a href="trade.php">Trade</a></li>
    </ul>
  </div>
</nav>
    <div class = "container">

		<form action="operation.php" method="post" name="Login_Form" class="form-signin">       
		  <div class="form-group">
		    <label for="apikey">Api Key:</label>
		    <input type="text" id="apikey" class="form-control" name="apikey" placeholder="Api Key" required="" />
		  </div>
		  <div class="form-group">
		    <label for="secret">Secret:</label>
		    <input type="password" class="form-control" id="secret" name="secret" placeholder="Secret" required=""/>     		  
		  </div>
		  <button type="submit" name="validate" value="Validate" class="btn btn-default">Submit</button>
		</form>

	</div>
	</body>
	<link rel="stylesheet" type="text/css" href="assets/css/style.css">
	<script type="text/javascript" src="assets/js/script.js"></script>    
</html>
