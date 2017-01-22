<?php
require_once('include/api_btc_e.php');
include('include/config.php');

global $context;
global $api;
global $allPrices;
$allPrices = array(); 


$currencyPair = array('btc_usd', 'ltc_usd', 'ltc_btc', 'eur_usd');
 
foreach($currencyPair as $cPair) {
    $allPrices[$cPair]['lastPrice'] = $api->getLastPrice($cPair);
    $allPrices[$cPair]['buyPrice'] = $api->getBuyPrice($cPair);
    $allPrices[$cPair]['sellPrice'] = $api->getSellPrice($cPair);
    $allPrices[$cPair]['highPrice'] = $api->getHighPrice($cPair);
    $allPrices[$cPair]['lowPrice'] = $api->getLowPrice($cPair);
}

?>