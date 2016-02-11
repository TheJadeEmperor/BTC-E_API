<?php
/*
Trading Strategy

Restrictions
* Rest period - after a sell, do not buy for at least 12 hours
prevents constant buy/sell orders and losing money 
* Stop loss - look for ATH and stop loss exit is 1% below ATH 

api_trade_data fields
* last_action - buy/sell 
* trade_signal - active / rest / rest_until_downtrend
* last_updated - when the signal was last updated 
*/


function sendMail($sendEmailBody) {
    $headers = 'From: alerts@bestpayingsites.com' . "\r\n" .
    'Reply-To: alerts@bestpayingsites.com' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

    $emailTo = '17182136574@tmomail.net';
    $mailSent = mail($emailTo, 'BTC-E Trade', $sendEmailBody, $headers);
    
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
    
    global $api, $totalBalance;
    
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
        echo $msg = $action.' '.$tradeAmt.' of '.$pair.' at price '.$latestPrice."\n".'balance: '.$totalBalance."\n".'time: '.date('Y-m-d H:i:s', time());
        sendMail($msg);
    };
}

/*
Updates the last action the program performed
 * $last_action = array(
 *      last_action - buy or sell
 *      last_price - price of trade
 *      trade_signal - rest or stop loss
 *      currency - ltc or btc
 *      exchange - btc-e or bitfinex
 *      last_updated - timestamp
 * )
*/
function update_last_action($last_action_data) {

    global $db, $context;
    
    $queryD = 'UPDATE '.$context['tradeDataTable'].' SET 
        last_action="'.$last_action_data['last_action'].'",
        last_price="'.$last_action_data['last_price'].'",            
        trade_signal="'.$last_action_data['trade_signal'].'",
        last_updated="'.date('Y-m-d H:i:s', time()).'"
        WHERE currency="'.$last_action_data['currency'].'" 
            AND exchange="btc-e"';
    
    $queryD = $db->query($queryD);
}

/*
Update the current trade signal 
*/
function update_trade_signal($last_action_data) {
    
    global $db, $context;
    
    $queryD = 'UPDATE '.$context['tradeDataTable'].' SET 
        trade_signal="'.$last_action_data['trade_signal'].'",
        last_updated="'.date('Y-m-d H:i:s', time()).'"
        WHERE currency="'.$last_action_data['currency'].'" 
            AND exchange="btc-e"';
    
    $queryD = $db->query($queryD);
}

include_once('include/api_btc_e.php');
include_once('include/config.php');

$btceAPI = $api = new BTCeAPI(BTCE_API_KEY, BTCE_API_SECRET);

$currencyPair = array('btc_usd', 'ltc_usd', 'ltc_btc');
 
foreach($currencyPair as $cPair) {
    $allPrices[$cPair]['lastPrice'] = $api->getLastPrice($cPair);
    $allPrices[$cPair]['buyPrice'] = $api->getBuyPrice($cPair);
    $allPrices[$cPair]['sellPrice'] = $api->getSellPrice($cPair);
    $allPrices[$cPair]['highPrice'] = $api->getHighPrice($cPair);
    $allPrices[$cPair]['lowPrice'] = $api->getLowPrice($cPair);
}

date_default_timezone_set('America/New_York');


$debug = $_GET['debug']; //debug mode
global $totalBalance, $api; //total balance in your account

if($debug == 1) {
    $output = '<< debug mode >>'; $newline = '<br>';
} 
else { //live mode
    $newline = "\n"; //output is sent via email \n is newline in email
}


//get options from api_options table
$queryO = $db->query('SELECT * FROM '.$context['optionsTable'].' ORDER BY opt');

$output .= $newline.$newline.'api_options'.$newline;
foreach($queryO as $opt) { 
    $output .= '[ '.$opt['opt'].': '.$opt['setting'].' ]';

    $btc_e_option[$opt['opt']] = $opt['setting'];
}

echo $output;

$acctInfo = $api->apiQuery('getInfo');
$acctFunds = $acctInfo['return']['funds'];

$currency = $btc_e_option['btc_e_currency'];
$pair = $currency.'_usd';

//database field
$price_field = 'btce_'.$currency; 

$queryMA = $db->query('SELECT (AVG('.$price_field.')) AS ma_7 FROM '.$context['pricesTable'].' WHERE count <= 7');
foreach($queryMA as $row) { 
    $ma_7 = $row['ma_7']; 
}

$queryMA = $db->query('SELECT (AVG('.$price_field.')) AS ma_30 FROM '.$context['pricesTable'].' WHERE count <= 30');
foreach($queryMA as $row) { 
    $ma_30 = $row['ma_30']; 
}

$latestPrice = $allPrices[$pair]['lastPrice'];
$btcPrice =  $allPrices['btc_usd']['lastPrice'];
$ltcPrice =  $allPrices['ltc_usd']['lastPrice'];

//sum total of account balance in all currencies
$totalBalance = $acctFunds['usd'] + $acctFunds['btc'] * $btcPrice + $acctFunds['ltc'] * $ltcPrice;

//how much btc/ltc you can buy
$tradable['btc'] = number_format($acctFunds['usd']/$btcPrice, 8); 
$tradable['ltc'] = number_format($acctFunds['usd']/$ltcPrice, 8); 


echo $newline.$newline;
echo 'current prices: '.$newline.'[btc: '.$btcPrice.'] [ltc: '.$ltcPrice.']'.$newline.$newline;
echo 'account balance: '.number_format($totalBalance, 2).$newline;
echo 'btc: '.number_format($acctFunds['btc'], 4).' - ltc '.number_format($acctFunds['ltc'], 4).' - usd: '.number_format($acctFunds['usd'], 2).$newline;
echo 'tradeable btc: '.number_format($tradable['btc'], 4).' | tradeable ltc: '.number_format($tradable['ltc'], 4).$newline;
echo '7_hour_sma: '.number_format($ma_7, 4).' | 30_hour_sma: '.number_format($ma_30, 4).$newline.$newline;

if($btc_e_option['btc_e_trading'] == 1) {
    echo 'btc_e trading is ON'.$newline;
}
else {
    echo 'btc_e trading is OFF'.$newline;
}


//determine if 12 hour rest period is over
$queryT = 'SELECT * FROM '.$context['tradeDataTable'].' WHERE currency = "'.$currency.'"
    AND exchange = "btc-e"';
$resT = $db->query($queryT);

foreach($resT as $t) { 
    $last_updated = $t['last_updated'];
    $trade_signal = $t['trade_signal'];
}

echo 'last_updated: '.$t['last_updated'].' | time now: '.date('Y-m-d H:i:s', time()).' | ';

$rest_period_over = 1; $hours = 0;
if($trade_signal == 'rest') {
    $datetime1 = date_create($t['last_updated']); //time of last action
    $datetime2 = date_create(date('Y-m-d H:i:s', time())); //time now
    $interval = date_diff($datetime1, $datetime2); //get the difference between the 2 timestamps
    $hours = $interval->format('%H'); //get the hours from the difference
    
    if($hours >= 12) { //rest period = 12
        $rest_period_over = 1; 
        
        $last_action_data = array(
            'trade_signal' => 'active',
            'currency' => $currency,
        );
        
        update_trade_signal($last_action_data);
        //update_last_action($last_action_data); 
    }
    else {
        $rest_period_over = 0;
    }
}
echo 'diff: '.$hours.' hours | rest period over: '.$rest_period_over.$newline;


if($btc_e_option['btc_e_trading'] == 1) //if trading is on - from options screen
if($rest_period_over == 1) //12 hour rest period is over 
if($ma_7 > $ma_30) { //uptrend signal
   
    if($debug == 1) //do not trade in debug mode
        $tradeAmt = '0.01';
    else
        $tradeAmt = $tradable[$currency]; //amount of tradatable btc/ltc
    
    echo '[ uptrend ][ tradeAmt '.$tradeAmt.' ][ '.$pair.' ]';
              
    $last_action_data = array(
        'last_action' => 'buy',
        'last_price' => $latestPrice,
        'trade_signal' => 'active',
        'currency' => $currency,
        'exchange' => 'btc-e'
    );
    
    if($debug != 1) //do not trade in debug mode
    if($acctFunds['usd'] > 0.01) { //if there is USD available for trading 
        
        if($trade_signal != 'rest_until_downtrend') {
            makeTrade($tradeAmt, $pair, 'buy', $latestPrice); 
            update_last_action($last_action_data); 
            echo '[ buy ]'.$newline;
        }
    }
    else {
        echo 'No balance to trade';
    }
    echo $newline;
    
    
    //========== stop loss ==========
    /* How to sell near the top
    - get the ATH (all time high) from 30m price table
    - set the stop loss at 1% below ATH
    - after stop loss hits, set the trade signal */

    
    //if current price is above MA_30 
        
        $queryATH = 'SELECT (MAX('.$price_field.')) AS ATH FROM '.$context['pricesTable30m'].' where time > "'.$last_updated.'"';
        $resultATH = $db->query($queryATH);
        
        foreach($resultATH as $row) { //get ATH = all time high 
            $ATH = $row['ATH']; 
        }
        
        //if current price is bigger than ATH
        if($latestPrice > $ATH) $ATH = $latestPrice;
        
        //stop loss price = 1.0% below ATH
        $stop = $ATH - $ATH * 0.01;
        echo 'ATH: '.number_format($ATH, 4).' | stop loss: '.number_format($stop, 4).' ';
        
        if($latestPrice <= $stop) { //price is under stop loss - sell!
            echo ' | stop loss exit'; 
            $tradeAmt = $acctFunds[$currency];
            makeTrade($tradeAmt, $pair, 'sell', $latestPrice);
            
            //rest_until_downtrend => after selling, do not buy anymore, no actions until downtrend
            $last_action_data = array(
                'last_action' => 'sell',
                'last_price' => $latestPrice,
                'trade_signal' => 'rest_until_downtrend', 
                'currency' => $currency,
                'exchange' => 'btc-e'
            );
            update_last_action($last_action_data);         
        }
        else 
            echo ' | no exit yet';
     //========== stop loss ==========
    
}//uptrend
else if ($ma_7 < $ma_30) { //downtrend signal
    
    if($debug == 1)
        $tradeAmt = 0.01;
    else
        $tradeAmt = $acctFunds[$currency]; //amount of btc/ltc in the account
            
    echo '[downtrend] [tradeAmt '.$tradeAmt.'] ['.$pair.'] ';
    
    $last_action_data = array(
        'last_action' => 'sell',
        'last_price' => $latestPrice,
        'trade_signal' => 'rest',
        'currency' => $currency,
        'exchange' => 'btc-e'
    );
    
    if($debug != 1) //do not trade in debug mode
    if($acctFunds[$currency] > 0.01) {
        makeTrade($tradeAmt, $pair, 'sell', $latestPrice);
        update_last_action($last_action_data);
        echo '[sell]';
    }
    else {
        echo 'No balance to trade';
    }
    echo $newline;
    
    if($trade_signal == 'rest_until_downtrend') {
        $last_signal_data = array(
            'trade_signal' => 'active',
            'currency' => $currency,
        );
        
        update_trade_signal($last_signal_data);
    }
}


?>