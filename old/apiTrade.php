<?php
include('apiPrices.php');

function whichCurrency($pair) {
    switch($pair) {
        case 'btc_usd':
            $currency = 'btc';
            break;
        case 'ltc_usd':
            $currency = 'ltc';
            break;
        case 'nvc_usd':
            $currency = 'nvc';
            break;
        case 'nmc_usd':
            $currency = 'nmc';
            break;
        default:
            $currency = 'usd';
    }
    
    return $currency;
}

function sendMail($sendEmailBody) {
    $headers = 'From: alerts@bestpayingsites.com' . "\r\n" .
    'Reply-To: alerts@bestpayingsites.com' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

    $emailTo = '17182136574@tmomail.net';
    $mailSent = mail($emailTo, 'Text alert', $sendEmailBody, $headers);
    
    if($mailSent) {
        $subject = 'Text alert sent';
    }
    else {
        $subject = 'Text alert NOT sent';
    }
    
    $emailTo = 'louie.benjamin@gmail.com'; 
    mail($emailTo, $subject, $sendEmailBody, $headers);
}

function makeTrade($tradeAmt, $pair, $action, $latestPrice) {
    
    global $api;
    
    if($action == 'buy') {
        
        try {
            $tradeResult = $api->makeOrder($tradeAmt, $pair, BTCeAPI::DIRECTION_BUY, $latestPrice);
        } 
        catch(BTCeAPIInvalidParameterException $e) {
            echo $e->getMessage();
        } 
        catch(BTCeAPIException $e) {
            echo $e->getMessage();
        }
    }
    else { //sell
       
        try {
            $tradeResult = $api->makeOrder($tradeAmt, $pair, BTCeAPI::DIRECTION_SELL, $latestPrice);  
        } 
        catch(BTCeAPIInvalidParameterException $e) {
            echo $e->getMessage();
        } 
        catch(BTCeAPIException $e) {
            echo $e->getMessage();
        }
    }

    if ($tradeResult['success'] == 1) {
        echo $msg = $action.' '.$tradeAmt.' of '.$pair.' at price '.$latestPrice;
        sendMail($msg);
    };
}


$debug = $_GET['debug'];

if($debug == 1) {
    echo '<< debug mode >>'; $newline = '<br>';
}
else {
    $newline = "\n";
}

//get options from api_options
$queryO = $c->query('SELECT * FROM '.$context['optionsTable'].' ORDER BY opt');

echo $newline.$newline.'api_options';
foreach($queryO as $opt){ 
    echo $newline.$opt['opt'].': '.$opt['setting'];

    $btc_e_option[$opt['opt']] = $opt['setting'];
}


$acctInfo = $api->apiQuery('getInfo');
$acctFunds = $acctInfo['return']['funds'];


$currency = $btc_e_option['btc_e_currency'];
$pair = $currency.'_usd';

//database field
$price_field = 'btce_'.$currency; 

$queryMA = $c->query('SELECT (AVG('.$price_field.')) AS ma_7 FROM '.$context['pricesTable'].' WHERE count <= 7');
foreach($queryMA as $row) { 
    $ma_7 = $row['ma_7']; }

$queryMA = $c->query('SELECT (AVG('.$price_field.')) AS ma_30 FROM '.$context['pricesTable'].' WHERE count <= 30');
foreach($queryMA as $row) { 
    $ma_30 = $row['ma_30']; }


//echo $pair;
$latestPrice = $allPrices[$pair]['lastPrice'];
$btcPrice =  $allPrices['btc_usd']['lastPrice'];
$ltcPrice =  $allPrices['ltc_usd']['lastPrice'];


//sum total of account balance in all currencies
$totalBalance = $acctFunds['usd'] + $acctFunds['btc'] * $btcPrice + $acctFunds['ltc'] * $ltcPrice;

//how much btc/ltc you can buy
$tradable['btc'] = number_format($acctFunds['usd']/$btcPrice, 8); 
$tradable['ltc'] = number_format($acctFunds['usd']/$ltcPrice, 8); 


echo $newline.$newline;
echo 'current prices: '.$newline.'btc: '.$btcPrice.' - ltc: '.$ltcPrice.$newline.$newline;
echo 'account balance: '.$totalBalance.$newline;
echo 'btc: '.$acctFunds['btc'].' - ltc '.$acctFunds['ltc'];
echo 'usd: '.$acctFunds['usd'].$newline.$newline;
echo 'tradeable btc: '.$tradable['btc'].' - tradeable ltc: '.$tradable['ltc'].$newline;
echo '7_hour_sma: '.$ma_7.$newline;
echo '30_hour_sma: '.$ma_30.$newline.$newline;


if($btc_e_option['btc_e_trading'] == 1) {
    echo 'btc_e trading is on'.$newline;
}
else {
    echo 'btc_e trading is off'.$newline;
}

//new stuff
/*
echo 'top 0.5% of sma_7: '.($ma_7 + $ma_7 * 0.005).' -
    bottom 0.5% of sma_7: '.($ma_7 - $ma_7 * 0.005).$newline.$newline;
*/

if($btc_e_option['btc_e_trading'] == 1)
if($ma_7 > $ma_30) { //buy signal
   
    if($debug == 1)
        $tradeAmt = '0.01';
    else
        $tradeAmt = $tradable[$currency]; 
    
    echo '[buy] [tradeAmt '.$tradeAmt.'] ['.$pair.']'.$newline;
              
    if($debug != 1) //do not trade in debug mode
    if($acctFunds['usd'] > 0) {
        makeTrade($tradeAmt, $pair, 'buy', $latestPrice); 
    }
    else {
        echo 'No balance to trade';
    }
}
else if ($ma_7 < $ma_30) { //sell signal
    
    if($debug == 1)
        $tradeAmt = 0.01;
    else
        $tradeAmt = $acctFunds[$currency]; //amount of btc/ltc in the account
            
    echo '[sell] [tradeAmt '.$tradeAmt.'] ['.$pair.'] '.$newline;
         
    echo $pair.' '.$lastestPrice;
    if($debug != 1) //do not trade in debug mode
    if($acctFunds[$currency] > 0) {
        makeTrade($tradeAmt, $pair, 'sell', $latestPrice);
    }
    else {
        echo 'No balance to trade';
    }
}


?>