<?php
include('include/config.php');

date_default_timezone_set ( 'America/New_York' );
$time = date('h:i a', time());

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

function getBitfinexPrice($currency) {
    $urlTickerPrice = 'https://api.bitfinex.com/v1/pubticker/'.$currency.'usd';
    $urlJSON = file_get_contents($urlTickerPrice);
    $currentPrice = json_decode($urlJSON);
    return $currentPrice->last_price;
} 

function getCEXPrice($currency) {
    $urlTickerPrice = 'https://cex.io/api/ticker/'.$currency.'/USD';  
    $urlJSON = retrieveJSON($urlTickerPrice);
    return $urlJSON['last'];
}


//Bitfinex prices
$bitfinexPriceBTC = getBitfinexPrice('btc');
$bitfinexPriceLTC = getBitfinexPrice('ltc');

//CEX.IO prices
$cexPriceBTC = getCEXPrice('BTC');
$cexPriceLTC = getCEXPrice('LTC');
//echo $cexPriceBTC.' '.$cexPriceLTC;

// exit; 

//shift all prices down 1 count
$upd = 'UPDATE '.$context['pricesTable2h'].' as new
JOIN (SELECT * FROM '.$context['pricesTable2h'].' AS old) 
AS old ON old.count = new.count-1
SET new.btce_btc = old.btce_btc, new.btce_ltc = old.btce_ltc, 
new.bitfinex_btc = old.bitfinex_btc, new.bitfinex_ltc = old.bitfinex_ltc, 
new.cex_btc = old.cex_btc, new.cex_ltc = old.cex_ltc, 
new.time = old.time';

$db->exec($upd);

echo 'time: '.$time." \n";
echo 'CEX.IO | BTC: '.$cexPriceBTC.' | LTC: '.$cexPriceLTC."\n";
echo 'Bitfinex | BTC: '.$bitfinexPriceBTC.' | LTC: '.$bitfinexPriceLTC."\n";

//if for some reason, the price is not recorded - ie. the website is down
if($cexPriceBTC == 0) { //if cex.io crashes, get price from bitfinex
    $cexPriceBTC = $bitfinexPriceBTC;
    $cexPriceLTC = $bitfinexPriceBTC;
}

if($bitfinexPriceBTC == 0) { //if bitfinex crashes, get price from cex.io
    $bitfinexPriceBTC = $cexPriceBTC;
    $bitfinexPriceBTC = $cexPriceLTC;
}

//input latest prices into count = 1
$upd = "UPDATE ".$context['pricesTable2h']." SET time = '".date("Y-m-d H:i:s", time())."', 
    cex_btc = '$cexPriceBTC', 
    cex_ltc = '$cexPriceLTC', 
    bitfinex_btc = '$bitfinexPriceBTC',        
    bitfinex_ltc = '$bitfinexPriceLTC'
    WHERE count = 1";

$db->exec($upd);

?>