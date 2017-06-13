<?php
	session_start();
	require __DIR__.'/library/bittrex/Client.php';

	if(isset($_POST['validate']) && !empty($_POST['apikey']) && !empty($_POST['secret'])){
		
			$_SESSION['key'] 	= $_POST['apikey'];
			$_SESSION['secret'] = $_POST['secret'];
			
			echo $_SESSION['key'] = '6ebadd9db0504586bf7f26a5adae9ccf';
			$_SESSION['secret'] = 'f04b39348a884448837641d0c2504588';
			
			$_SESSION['bittrex'] = new Client ($_SESSION['key'], $_SESSION['secret']);
			try{
				$balance = $_SESSION['bittrex']->getBalances();
				header('Location:account.php');
			}catch(Exception $e){
				header('Location:index.php');
			}


	}else{
		header('Location:index.php');
	}


			/*$key = 'b67685fe5db54a97a8918e8e36b6f56d';
			$secret = '2429c5afdfda408881b8a0f720a91765';*/


	if(isset($_GET['task']) && $_GET['task']==='logout'){

		session_destroy();
		header('Location:index.php');
		
	}

?>