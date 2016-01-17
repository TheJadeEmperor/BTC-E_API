<?php
function array_debug($array) {
    echo '<pre>';
    print_r($array);
    echo '</pre>';
}

function sendMail($sendEmailBody) {
    $headers = 'From: alerts@bestpayingsites.com' . "\r\n" .
    'Reply-To: alerts@bestpayingsites.com' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

    $emailTo = '17182136574@tmomail.net';
    $mailSent = mail($emailTo, 'TEST', $sendEmailBody, $headers);
    
    if($mailSent) {
        $subject = 'Text alert sent';
    }
    else {
        $subject = 'Text alert NOT sent';
    }
    
    $emailTo = 'louie.benjamin@gmail.com'; 
    mail($emailTo, $subject, $sendEmailBody, $headers);
}

function getBitfinexPrice($currency) {
    //get price of btc/ltc
    $apiURL = 'https://api.bitfinex.com/v1/pubticker/'.$currency.'usd';
   
    $contentsURL = file_get_contents($apiURL);
   
    $priceData = json_decode($contentsURL);
    
    $latestPrice = $priceData->last_price;
    return $latestPrice;
}

/*
Updates the last action the program performed - buy or sell 
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
            AND exchange="bitfinex"';
    
    $queryD = $db->query($queryD);
}



include_once('include/api_bitfinex.php');
include('include/config.php');

define('API_KEY','lhB8h0aZSH8v49CbIM6Ib3bee6tHQ8aZZbmbTjf2OYM');
define('API_SECRET','VHcTwmMwEoy2jBhGvMcldBZ5dy9OjosKuynmut05dcw');
$call = new Bitfinex(API_KEY, API_SECRET);

$debug = $_GET['debug'];

if($debug == 1) {
    echo '<< debug mode >>'; $newline = '<br>';
}
else {
    $newline = "\n";
}


//get trading options from api_options
$queryO = $db->query('SELECT * FROM '.$context['optionsTable'].' ORDER BY opt');

echo $newline.$newline.'api_options'.$newline;
foreach($queryO as $opt) { 
    echo '['.$opt['opt'].': '.$opt['setting'].']';

    $bitfinex_option[$opt['opt']] = $opt['setting'];
}

echo $newline; 

$currency = $bitfinex_option['bitfinex_currency']; //currency defined in api_options
$symbol = strtoupper($currency.'usd'); //currency symbol used for making orders


//database field
$price_field = 'bitfinex_'.$currency; 

//get moving averages
$queryMA = $db->query('SELECT (AVG('.$price_field.')) AS ma_7 FROM '.$context['pricesTable'].' WHERE count <= 7');
foreach($queryMA as $row) { 
    $ma_7 = $row['ma_7']; 
}

$queryMA = $db->query('SELECT (AVG('.$price_field.')) AS ma_30 FROM '.$context['pricesTable'].' WHERE count <= 30');
foreach($queryMA as $row) { 
    $ma_30 = $row['ma_30']; 
}

$btcPrice = getBitfinexPrice('btc'); //current market BTC price
$ltcPrice = getBitfinexPrice('ltc'); //current market LTC price

if($currency == 'btc') {
    $latestPrice = $btcPrice;
}
else {
    $latestPrice = $ltcPrice;
}
 
$acctFunds = $call->get_balances();

//funds in margin balance
$acctBTC = $acctFunds[4]['amount'];
$acctLTC = $acctFunds[5]['amount'];
$acctUSD = $acctFunds[6]['amount'];


$acc = $call->margin_infos();
$marginUSD = $acc[0]['margin_limits'][0]['tradable_balance'];

//how much btc/ltc you can buy - determined from margin balance
$tradable['btc'] = number_format($marginUSD/$btcPrice, 4); 
$tradable['ltc'] = number_format($marginUSD/$ltcPrice, 4); 

$output .= $newline.$newline;
$output .= 'current prices: '.$newline.'btc: '.number_format($btcPrice, 4).' - ltc: '.number_format($ltcPrice, 4).$newline.$newline;
$output .= 'account balance: '.$totalBalance.$newline;
$output .= 'usd: '.number_format($acctUSD, 2).' - margin: '.number_format($marginUSD, 4).$newline;
$output .= 'tradeable btc: '.$tradable['btc'].' | tradeable ltc: '.$tradable['ltc'].$newline.$newline;
$output .= '7_hour_sma: '.number_format($ma_7, 4).' - 30_hour_sma: '.number_format($ma_30, 4).$newline.$newline;


//get active positions id
$active_pos = $call->active_positions();
$position_id = $active_pos[0]['id'];

if($position_id) {
    $tradeAmt = abs($active_pos[0]['amount']); 
}
else { 
    $tradeAmt = $tradable[$currency];
}


if($bitfinex_option['bitfinex_trading'] == 1)
if($ma_7 > $ma_30) { //uptrend signal

    $output .= '[uptrend][tradeAmt: '.$tradeAmt.']'.$newline;
    
    $last_action_data = array(
        'last_action' => 'buy',
        'last_price' => $latestPrice,
        'trade_signal' => '',
        'currency' => $currency,
    );
    
    if($debug != 1) { 
        
        $data = array( //do not trade in debug mode
            'symbol' => $symbol,
            'price' => $ltcPrice,
            'amount' => "$tradeAmt",
            'side' => 'buy', //buying side
            'type' => 'fill-or-kill', //margin buying
            'exchange' => 'bitfinex'
        );

        $msg = $call->new_order($data); //new long position
    
        $output .= array_debug($msg).$newline;
        
        if($active_pos[0]['amount'] < 0 //negative # means short position
                && $active_pos[0]['status'] == 'ACTIVE') {
            $data['position_id']  = $position_id;      
            $position_msg = $call->claim_positions($data);  //close short position

            echo $position_msg['message'];

            if($position_msg['message']['is_live'] == 1) {
                sendMail('Buy '.$tradeAmt.' '.$currency.' at '.$ltcPrice);
                update_last_action($last_action_data);
            }
        }
        
    }
}//uptrend
else if ($ma_7 < $ma_30) { //downtrend signal

    $output .= '[downtrend][tradeAmt: '.$tradeAmt.']'.$newline;
    
    $last_action_data = array(
        'last_action' => 'sell',
        'last_price' => $latestPrice,
        'trade_signal' => '',
        'currency' => $currency,
    );
    
    if($debug != 1) { //do not trade in debug mode
 
        $data = array(
            'symbol' => $symbol,
            'price' => $ltcPrice,
            'amount' => "$tradeAmt",
            'side' => 'sell', //selling side = shorting
            'type' => 'fill-or-kill', //margin trading  
            'exchange' => 'bitfinex'
        );

        $msg = $call->new_order($data); //new short position
        
        $output .= array_debug($msg).$newline;
        
        if($active_pos[0]['amount'] > 0 && $active_pos[0]['satus'] == 'ACTIVE') {
            $data['position_id']  = $position_id;      
            $position_msg = $call->claim_positions($data);   //close long position 
            
            echo $position_msg['message'];

            if($position_msg['message']['is_live'] == 1) {
                sendMail('Sell '.$tradeAmt.' '.$currency.' at '.$ltcPrice);
                update_last_action($last_action_data);
            }
        }   
    }
}

echo $output;

echo 'active positions: '; array_debug($active_pos);

?>