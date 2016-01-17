<?php
/*
 * 
Trading Strategy

* Restrictions - after a sell, do not buy for at least 12 hours
prevents constant buy/sell orders and losing money 

api_trade_data fields
last_action - buy/sell 
signal - hold = hold for 12 hours
*/

include('apiPrices.php');

/*
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
*/

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

/*
Updates the last action the program performed - buy or sell
*/
function update_last_action($last_action, $trade_signal, $currency) {

    global $db, $context;
    
    $queryD = 'UPDATE '.$context['tradeDataTable'].' SET 
        last_action="'.$last_action.'",
        trade_signal="'.$trade_signal.'",
        last_updated="'.date('Y-m-d H:i:s', time()).'"
        WHERE currency="'.$currency.'" ';
    
    $queryD = $db->query($queryD);
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

echo $newline.$newline.'api_options'.$newline;
foreach($queryO as $opt) { 
    echo '['.$opt['opt'].': '.$opt['setting'].']';

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

//determine if 12 hour rest period is over
$queryT = 'SELECT * FROM '.$context['tradeDataTable'].' WHERE currency="'.$currency.'"';
$resT = $db->query($queryT);

foreach($resT as $t) { 
    $last_updated = $t['last_updated'];
    $trade_signal = $t['trade_signal'];
}

echo 'last_updated: '.$t['last_updated'].' - time now: '.date('Y-m-d H:i:s', time()).' - ';

if($trade_signal == 'rest') {
    $datetime1 = date_create($t['last_updated']);
    $datetime2 = date_create(date('Y-m-d H:i:s', time()));
    $interval = date_diff($datetime1, $datetime2);
    $hours = $interval->format('%H'); 
    
    if($hours >= 12) { //rest period = 12
        $rest_period_over = 1;
        update_last_action('sell', '', $currency);
    }
    else {
        $rest_period_over = 0;
    }
}
echo 'diff: '.$hours.' hours - rest period over: '.$rest_period_over.$newline;


if($btc_e_option['btc_e_trading'] == 1) //if trading is on
if($rest_period_over == 1) //12 hour rest period is over
if($ma_7 > $ma_30) { //buy signal
   
    if($debug == 1)
        $tradeAmt = '0.01';
    else
        $tradeAmt = $tradable[$currency]; //amount of tradatable btc/ltc
    
    echo '[buy] [tradeAmt '.$tradeAmt.'] ['.$pair.']'.$newline;
              
    if($debug != 1) //do not trade in debug mode
    if($acctFunds['usd'] > 0) {
        makeTrade($tradeAmt, $pair, 'buy', $latestPrice); 
        update_last_action('buy', '', $currency);
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
         
    if($debug != 1) //do not trade in debug mode
    if($acctFunds[$currency] > 0) {
        makeTrade($tradeAmt, $pair, 'sell', $latestPrice);
        update_last_action('sell', 'rest', $currency);
    }
    else {
        echo 'No balance to trade';
    }
}

?>