<?php
include('include/api_database.php');
include('include/api_poloniex.php');
include('include/config.php');

$candleData = new Database($db);


//$candleData->sendMail();

include('index1.html');
?>