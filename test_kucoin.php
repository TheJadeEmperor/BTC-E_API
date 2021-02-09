
<?php
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'api_kucoin.php');
include($dir.'functions.php');
include($dir.'config.php');
  

//kucoin subaccount
$key = $kucoin1_key;
$secret = $kucoin1_secret;
$passphrase = $kucoin1_passphrase;


checkBalance();
MakeOrder();
//CancelOrder();
?>