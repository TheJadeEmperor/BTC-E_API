<?php
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'api_bittrex.php');
include($dir.'config.php');

//connect to Bittrex
$bittrex = new Client ($bittrex_api_key, $bittrex_api_secret);

$bittrex->getMarkets();

?>