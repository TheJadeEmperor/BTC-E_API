<?php
include('include/config.php');

function retrieveJSON($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    $result = curl_exec($ch);
    curl_close($ch);

    $json = json_decode($result, true);
    return $json;
}

//get last price for BTC-E currency pair
function getLastPrice($pair) {
    global $public_api;
    $json = retrieveJSON($public_api.'ticker/'.$pair);        
    return $json[$pair]['last'];
}

global $public_api;
$public_api = 'https://btc-e.com/api/3/';

//BTC-E prices
$btcPrice = getLastPrice('btc_usd');
$ltcPrice = getLastPrice('ltc_usd');

//Bitfinex prices
$urlBTC = 'https://api.bitfinex.com/v1/pubticker/btcusd';
$urlLTC = 'https://api.bitfinex.com/v1/pubticker/ltcusd';
    
$urlBTC = file_get_contents($urlBTC);
$urlLTC = file_get_contents($urlLTC);

$allPrices['bitfinex_btc'] = json_decode($urlBTC);
$allPrices['bitfinex_ltc'] = json_decode($urlLTC);

//Bitfinex prices
$bitfinex_btc = $allPrices['bitfinex_btc']->last_price;
$bitfinex_ltc = $allPrices['bitfinex_ltc']->last_price;


date_default_timezone_set ( 'America/New_York' );
$time = date('h:i a', time());

//shift all prices down 1 count
$upd = 'UPDATE '.$context['pricesTable'].'
JOIN (SELECT * FROM api_prices AS old) 
AS old ON old.count = api_prices.count-1
SET api_prices.btce_btc = old.btce_btc, api_prices.btce_ltc = old.btce_ltc, 
api_prices.bitfinex_btc = old.bitfinex_btc, api_prices.bitfinex_ltc = old.bitfinex_ltc, 
api_prices.time = old.time';

$db->exec($upd);

echo 'time: '.$time." \n";
echo '[BTC-E] [BTC: '.$btcPrice.'] [LTC: '.$ltcPrice.']'."\n";
echo '[Bitfinex] [BTC: '.$bitfinex_btc.'] [LTC: '.$bitfinex_ltc.']'."\n";

if(empty($btcPrice)) {
    $btcPrice = $bitfinex_btc;
}

if(empty($ltcPrice)) {
    $ltcPrice = $bitfinex_ltc;
}

//input latest prices into count = 1
$upd = "UPDATE ".$context['pricesTable']." SET time = now(), 
    btce_btc = '$btcPrice', 
    btce_ltc = '$ltcPrice', 
    bitfinex_btc = '$bitfinex_btc',        
    bitfinex_ltc = '$bitfinex_ltc'
    WHERE count = 1";

$db->exec($upd);

?>