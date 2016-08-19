<?php
	// Include Cex.io PHP API
	require_once "cexapi.php"; 
	
	// Create API Object
	$api = new cexApi("Your user id", "Key", "Secret");    // change this user details to your account details

	echo "<pre>", json_encode($api->place_order()), "</pre>";
	echo "<pre>", json_encode($api->cancel_order()), "</pre>"; 
	echo "<pre>", json_encode($api->balance()), "</pre>"; 
	
 
?>