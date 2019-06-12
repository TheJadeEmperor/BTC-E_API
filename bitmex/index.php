<?php

// set display errors status
ini_set('display_errors', 1); // 1-turn on all error reporings 0-turn off all error reporings
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

require_once 'BitMex.php';

$key = "";
$secret = "";

$bitmex = new BitMex($key, $secret);

//var_dump($bitmex->getWallet());

$type = "";
$side = "";
$price = "";
$quantity = "";

$bitmex->createOrder($type, $side, $price, $quantity, $maker = false);
