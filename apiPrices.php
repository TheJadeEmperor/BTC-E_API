<?php
require_once('include/api_btc_e.php');
include('include/config.php');

date_default_timezone_set('America/New_York');

global $context;
global $api;
global $allPrices;
$allPrices = array(); 

$context['tradeDataTable'] = 'api_trade_data';
$context['pricesTable'] = 'api_prices';
$context['optionsTable'] = 'api_options';

$api = new BTCeAPI(
    /*API KEY:    */    'D2OEJB9X-ZLTJ0UJV-CLAPX3IE-WFEC0W8I-8O139CAB', 
    /*API SECRET: */    '37f6137ae6e92b05b6effc3cdfd6969adb823a4324f7291f996f0daded8d3888'
);

$currencyPair = array('btc_usd', 'ltc_usd', 'ltc_btc', 'eur_usd');
 
foreach($currencyPair as $cPair) {
    $allPrices[$cPair]['lastPrice'] = $api->getLastPrice($cPair);
    $allPrices[$cPair]['buyPrice'] = $api->getBuyPrice($cPair);
    $allPrices[$cPair]['sellPrice'] = $api->getSellPrice($cPair);
    $allPrices[$cPair]['highPrice'] = $api->getHighPrice($cPair);
    $allPrices[$cPair]['lowPrice'] = $api->getLowPrice($cPair);
}

//$acctInfo = $api->apiQuery('getInfo');

?>