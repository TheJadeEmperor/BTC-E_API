<?php
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'api_poloniex.php');
include($dir.'config.php');
include($dir.'ez_sql_core.php');
include($dir.'ez_sql_mysql.php');



//requires the extension php_openssl to work
$polo = new poloniex($polo_api_key, $polo_api_secret);

echo $currencyPair = 'BTC_ETH';
$amt = '0.1';
$rate = '0.0348';


//$rrr = $polo->buy($currencyPair, '0.0569', $amt, 1);


$rrr = $polo->margin_sell($currencyPair, $rate, $amt, 1);
print_r($rrr);




?>