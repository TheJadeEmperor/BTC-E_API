<?php
include('include/functions.php');
include('include/config.php');

session_start();

if(!isset($_SESSION['admin']))//if not logged in, redirect back to login page
    header('Location: index.php'); 

//set timezone
date_default_timezone_set('America/New_York');

//page load ajax calls
$loadCronSendAlerts = 'load.php?page=cronSendAlerts&accessKey='.$accessKey;
$loadbtcTrades = 'load.php?page=btcTrades&accessKey='.$accessKey;
$loadPriceTable = 'load.php?page=priceTable&accessKey='.$accessKey;
$loadCronAutoTrade = 'load.php?page=cronAutoTrade&accessKey='.$accessKey;
$loadBalanceTable = 'load.php?page=balanceTable&accessKey='.$accessKey;

$loadNotesAjax = 'include/ajax.php?action=updateNotes';
$loadNotes = 'load.php?page=notes&accessKey='.$accessKey;

$loadBtrexBalance = 'load/btrexBalance.php?accessKey='.$accessKey;
$gateBalance = 'load/gateBalance.php?accessKey='.$accessKey;

$loadKCMBalance = 'load/kucoinBalance.php?page=kucoinMainBalance&accessKey='.$accessKey;
$loadKC1Balance = 'load/kucoinBalance.php?page=kucoin1Balance&accessKey='.$accessKey;
$loadKC2Balance = 'load/kucoinBalance.php?page=kucoin2Balance&accessKey='.$accessKey;
$loadKC3Balance = 'load/kucoinBalance.php?page=kucoin3Balance&accessKey='.$accessKey;
$loadKC4Balance = 'load/kucoinBalance.php?page=kucoin4Balance&accessKey='.$accessKey;
$loadKC5Balance = 'load/kucoinBalance.php?page=kucoin5Balance&accessKey='.$accessKey;

$linksModule = 'load/linksModule.php?accessKey='.$accessKey;
$webhook = 'load/webhook.php?accessKey='.$accessKey;

$loadingImage = '<img src="include/images/load.gif" id="loadingImage" />';
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
	<link rel="shortcut icon" type="image/png" href="include/dollar_sign.png" />

    <?
    include('scripts.html');
    ?>
</head>
<body>

<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1, shrink-to-fit=no"
    />

    <!-- Bootstrap CSS -->
    <link
      rel="stylesheet"
      href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"
      integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z"
      crossorigin="anonymous"
    />

    <!-- 🚨 Notice this link -->
    <link id="dark-theme-style" rel="stylesheet" />

    <title>Toggle Bootstrap Theme</title>
  </head>
  <body>
    <nav class="navbar navbar-transparent">
      <!-- 🚨 Notice the toggleTheme() function -->
      <a
        href="javascript:void()"
        class="btn btn-outline-info btn-lg ml-auto font-weight-bold"
        id="theme-toggler"
        onclick="toggleTheme()"
      ></a>
    </nav>

<div class="container">

<div class="row">
    <div class="col-6">
        <div id="btrexBalance">btrexBalance</div>
        
        <div id="kucoinMainBalance"><?=$loadingImage?></div>

        <div id="kucoin1Balance"><?=$loadingImage?></div>

        <div id="kucoin2Balance"><?=$loadingImage?></div>

        <div id="kucoin3Balance"><?=$loadingImage?></div>

        <div id="kucoin4Balance"><?=$loadingImage?></div>
        
        <div id="gateBalance"><?=$loadingImage?></div>
        <br />
    </div>

    <div class="col-6">
        <div class="col" id="linksModule">
            <?=$loadingImage?>
        </div>
    </div>

 

</div>

    <script
      src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"
      integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script
      src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"
      integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV"
      crossorigin="anonymous"></script>

    <!-- 🚨 Our custom JavaScript  -->
    <script src="light_dark_theme.js"></script>