<?php
include('include/functions.php');
include('include/config.php');

session_start();

if(!isset($_SESSION['admin']))//if not logged in, redirect back to login page
    header('Location: index.php'); 

//set timezone
date_default_timezone_set('America/New_York');
 
//alert mysql ajax calls
$createAlert = 'include/ajax.php?action=create';
$readAlert = 'include/ajax.php?action=read';
$updateAlert = 'include/ajax.php?action=update';
$deleteAlert = 'include/ajax.php?action=delete';

//trade mysql jax calls
$createTrade = 'include/ajax.php?action=createTrade';
$readTrade = 'include/ajax.php?action=readTrade';
$updateTrade = 'include/ajax.php?action=updateTrade';
$deleteTrade = 'include/ajax.php?action=deleteTrade';

//page load ajax calls
$loadCronSendAlerts = 'load.php?page=cronSendAlerts&accessKey='.$accessKey;
$loadbtcTrades = 'load.php?page=btcTrades&accessKey='.$accessKey;
$loadPriceTable = 'load.php?page=priceTable&accessKey='.$accessKey;
$loadCronAutoTrade = 'load.php?page=cronAutoTrade&accessKey='.$accessKey;
$loadBalanceTable = 'load.php?page=balanceTable&accessKey='.$accessKey;

$loadBitmexPositions = 'load.php?page=bitmexPositions&accessKey='.$accessKey;
$loadBitmexPositions2 = 'load.php?page=bitmexPositions2&accessKey='.$accessKey;

$loadNotesAjax = 'include/ajax.php?action=updateNotes';
$loadNotes = 'load.php?page=notes&accessKey='.$accessKey;

$loadKCMBalance = 'load/kucoinBalance.php?page=kucoinMainBalance&accessKey='.$accessKey;
$loadKC1Balance = 'load/kucoinBalance.php?page=kucoin1Balance&accessKey='.$accessKey;
$loadKC2Balance = 'load/kucoinBalance.php?page=kucoin2Balance&accessKey='.$accessKey;
$loadKC3Balance = 'load/kucoinBalance.php?page=kucoin3Balance&accessKey='.$accessKey;
$loadKC4Balance = 'load/kucoinBalance.php?page=kucoin4Balance&accessKey='.$accessKey;
$loadKC5Balance = 'load/kucoinBalance.php?page=kucoin5Balance&accessKey='.$accessKey;

$loadBtrexBalance = 'load/btrexBalance.php?accessKey='.$accessKey;

$loadingImage = '<img src="include/images/load.gif" />';
?>
<head>
	<title>BTC API Dashboard</title>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">

	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">

	<!-- JQueryUI -->
	<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css" />

	<script src="//code.jquery.com/jquery-latest.min.js" type='text/javascript' /></script>

	<script src="include/jquery-ui/ui/jquery-ui.js"></script>
	
    <link rel="stylesheet" href="include/admin.css" />
	<link rel="shortcut icon" type="image/png" href="include/dollar_sign.png">

    <?
    include('scripts.html');
    ?>
</head>
<body>
<div class="container">

<div class="row">
    <div class="col-6">
        <div id="btrexBalance">btrexBalance</div>
        
        <div id="kucoinMainBalance"><?=$loadingImage?></div>

        <div id="kucoin1Balance"><?=$loadingImage?></div>

        <div id="kucoin2Balance"><?=$loadingImage?></div>

        <div id="kucoin3Balance"><?=$loadingImage?></div>

        <div id="kucoin4Balance"><?=$loadingImage?></div>

        <div id="kucoin5Balance"><?=$loadingImage?></div>
        
        <br />
    </div>

    <div class="col">
        <h2>Links</h2>

        <p align="left"><a href="https://www.tradingview.com/chart/gghdcAkO/" target="_blank">Chart 1</a>
							
        <br /><br />			

        <a href="https://www.tradingview.com/chart/HRbO8WTw/" target="_blank">Chart 2</a>

        <br /><br />

        <a href="https://trade.kucoin.com/ADA-USDT" target="_blank">Kucoin Trade</a>
        </p>
    </div>
</div>