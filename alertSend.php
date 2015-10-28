<?
/* 
 * Send email alerts hourly for the price of BTC and LTC
 */

require_once('btce-api.php');
include('include/mysql.php');
include('include/config.php');

$api = new BTCeAPI(
    /*API KEY:    */    'P4ZM898V-DJSR3WKJ-CFVK9VKM-5ISFP5KO-ZVQLXW1P', 
    /*API SECRET: */    'bd6c1c022c334572691b57681c71a4734f08e5d2cddf1de3a8481e6be10d76df'
);


$btcUSD['lastPrice'] = $api->getLastPrice('btc_usd');
$btcUSD['highPrice'] = $api->getHighPrice('btc_usd');
$btcUSD['lowPrice'] = $api->getLowPrice('btc_usd');


$ltcUSD['lastPrice'] = $api->getLastPrice('ltc_usd');
$ltcUSD['highPrice'] = $api->getHighPrice('ltc_usd');
$ltcUSD['lowPrice'] = $api->getLowPrice('ltc_usd');


$ltcBTC['lastPrice'] = $api->getLastPrice('ltc_btc');
$ltcBTC['highPrice'] = $api->getHighPrice('ltc_btc');
$ltcBTC['lowPrice'] = $api->getLowPrice('ltc_btc');



$subject = 'BTC: '.$btcUSD['lastPrice'];
$sendEmailBody = 'LTC: '.$ltcUSD['lastPrice'].' / LTC/BTC: '.$ltcBTC['lastPrice'];


$headers = 'From: alerts@bestpayingsites.com' . "\r\n" .
    'Reply-To: alerts@bestpayingsites.com' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

echo $headers;

if($sendEmailBody) {
    $emailTo = '17182136574@tmomail.net'; //text to phone number
    $mailSent = mail($emailTo, $subject, $sendEmailBody, $headers);
    
    if($mailSent) {
        $subjectEmail = 'Text alert sent';
    }
    else {
        $subjectEmail = 'Text alert NOT sent';
    }
    
    $emailTo = 'louie.benjamin@gmail.com'; 
    echo mail($emailTo, $subjectEmail, $sendEmailBody, $headers);
}

?>