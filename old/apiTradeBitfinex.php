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

function makeTrade($tradeAmt, $pair, $action, $latestPrice) {
    
    global $totalBalance, $bitfinexAPI;
        
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
    
    $resultD = $db->query($queryD);
}


include_once('include/api_bitfinex.php');
include_once('include/config.php');

$bitfinexAPI = new Bitfinex(BITFINEX_API_KEY, BITFINEX_API_SECRET);

$debug = $_GET['debug'];
global $totalBalance, $bitfinexAPI; //total balance in your account

if($debug == 1) { //debug mode - no trading, output only
    echo '<< debug mode >>'; $newline = '<br>';
}
else { //live mode - output is sent to email - \n is newline in emails
    $newline = "\n";
}


//get trading options from api_options
$queryO = $db->query('SELECT * FROM '.$context['optionsTable'].' ORDER BY opt');

echo $newline.$newline.'api_options'.$newline;
foreach($queryO as $opt) { 
    echo '[ '.$opt['opt'].': '.$opt['setting'].' ]';

    $bitfinex_option[$opt['opt']] = $opt['setting'];
}

$currency = $bitfinex_option['bitfinex_currency']; //currency defined in api_options
$symbol = strtoupper($currency.'usd'); //currency symbol used for making orders


//database price field
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

$acctFunds = $bitfinexAPI->get_balances(); 
$acctMargin = $bitfinexAPI->margin_infos(); 

//array_debug($acctFunds);
//array_debug($acc);

//funds in margin balance
$acctBTC = $acctFunds[4]['amount'];
$acctLTC = $acctFunds[5]['amount'];
$acctUSD = $acctFunds[7]['amount'];

$marginUSD = $acctMargin[0]['margin_limits'][0]['tradable_balance'];

//how much btc/ltc you can buy - determined from margin balance
$tradable['btc'] = number_format($marginUSD/$btcPrice, 4); 
$tradable['ltc'] = number_format($marginUSD/$ltcPrice, 4); 

$output .= $newline.$newline;
$output .= 'current prices: '.$newline.'btc: '.number_format($btcPrice, 4).' | ltc: '.number_format($ltcPrice, 4).$newline.$newline;
$output .= 'account balance: '.number_format($acctUSD, 2).' '.$newline;
$output .= 'margin: '.number_format($marginUSD, 4).' | ';
$output .= 'tradeable btc: '.$tradable['btc'].' | tradeable ltc: '.$tradable['ltc'].$newline.$newline;
$output .= '7_hour_sma: '.number_format($ma_7, 4).' | 30_hour_sma: '.number_format($ma_30, 4).$newline.$newline;


$bitfinex_trading = $bitfinex_option['bitfinex_trading'];
if($bitfinex_trading == 1) {
    $output .= 'Bitfinex trading is ON';
}
else {
    $output .= 'Bitfinex trading is OFF';
}
$output .= $newline;


//get ATH for stop loss
$queryATH = 'SELECT (MAX('.$price_field.')) AS ATH FROM '.$context['pricesTable30m'].' where time > "'.$last_updated.'"';
$resultATH = $db->query($queryATH);
        
foreach($resultATH as $row) { //get ATH = all time high 
    $ATH = $row['ATH']; 
}


//get ATL for stop loss
$queryATL = 'SELECT (MIN('.$price_field.')) AS ATL FROM '.$context['pricesTable30m'].' where time > "'.$last_updated.'"';
$resultATL = $db->query($queryATL);
        
foreach($resultATL as $row) { //get ATL = all time low 
    $ATL = $row['ATL']; 
}


//get active positions id
$active_pos = $bitfinexAPI->active_positions();
$position = $active_pos[0];
$position_id = $active_pos[0]['id'];


if($position['id']) { //if there is an active position, the trade amount is the position's amount
    $tradeAmt = abs($position['amount']); 
}
else { //trade amount is the margin divided by the latestPrice 
    $tradeAmt = $marginUSD/$latestPrice - 0.01;
    $tradeAmt = $tradeAmt * 0.95; //don't use the entire balance
//    $tradeAmt = 0.16;
}

//echo ' '.$marginUSD/$latestPrice .' ';

if($ma_7 > $ma_30) { //uptrend signal

    $output .= '[ uptrend ][ tradeAmt: '.$tradeAmt.' ][ '.$currency.' ]';


    //data recorded for api_trade_data table
    $last_action_data = array(
        'last_action' => 'buy',
        'last_price' => $latestPrice,
        'trade_signal' => 'uptrend',
        'currency' => $currency,
    );

    $data = array( 
        'symbol' => $symbol,
        'price' => $latestPrice,
        'amount' => "$tradeAmt",
        'side' => 'buy', //buying side
        'type' => 'limit', //margin buying
        'exchange' => 'bitfinex'
    );

    //if trading is turned on
    if($debug != 1 && $bitfinex_trading == 1) { //do not trade in debug mode
 
        if($marginUSD > $acctUSD) { //if margin is available
            $newTrade = $bitfinexAPI->new_order($data); //new long position
        
            if($newTrade['is_live'] == 1) { //if trade is live
                sendMail('[Bitfinex] Buy '.$tradeAmt.' '.$currency.' at '.$latestPrice);
                update_last_action($last_action_data); //update the last action

                $output .= '[ buy ]'.$newline;
                $output .= array_debug($newTrade).$newline;
            }
        }
        else { //no balance left to trade
            $output .= ' no balance to trade ';
        }
        
        //if active postion - claim short/long margin position
        if($position['amount'] < 0 //negative # means short position
                && $position['status'] == 'ACTIVE') {
            $data['position_id'] = $position_id;      

            $claimPos = $bitfinexAPI->claim_positions($data);  //close short position

            echo $newline.$claimPos['message'].$newline;

        }
    }
    
    //stop loss 
    if($latestPrice > $ma_30) {
        //stop loss (long pos) is 2% below ATH
        $stopHigh = $ATH - $ATH * 0.02;

        $output .= $newline.'ATH: '.number_format($ATH, 4).' | stop loss: '.number_format($stopHigh, 4).' | ';
        if($latestPrice <= $stopHigh) {
            $output .= ' stop loss exit';

            if($debug != 1 && $bitfinex_trading == 1) {
                $data['position_id'] = $position_id; 
                $claimPos = $bitfinexAPI->claim_positions($data);
            
                echo $newline.$claimPos['message'].$newline;
            }
        }
        else {
            $output .= ' no exit yet';
        }
        $output .= $newline;
    }
}//uptrend
else if ($ma_7 < $ma_30) { //downtrend signal

    $output .= '[ downtrend ][ tradeAmt: '.$tradeAmt.' ][ '.$currency.' ]';
    
    $last_action_data = array(
        'last_action' => 'sell',
        'last_price' => $latestPrice,
        'trade_signal' => 'downtrend',
        'currency' => $currency,
    );
    
    
    if($debug != 1 && $bitfinex_trading == 1) { //do not trade in debug mode
 
        $data = array(
            'symbol' => $symbol,
            'price' => $latestPrice,
            'amount' => "$tradeAmt",
            'side' => 'sell', //selling side = shorting
            'type' => 'limit', //margin trading  
            'exchange' => 'bitfinex'
        );

        if($marginUSD > $acctUSD) { //if margin is available
            $newTrade = $bitfinexAPI->new_order($data); //new short position
        
            if($newTrade['is_live'] == 1) {
                sendMail('[Bitfinex] Sell '.$tradeAmt.' '.$currency.' at '.$latestPrice);
                update_last_action($last_action_data);

                $output .= '[ sell ]'.$newline;
                $output .= array_debug($newTrade).$newline;
            }
        }
        else {
            $output .= ' no balance to trade ';
        }
        

        if($position['amount'] > 0 //positve # means long position
                && $position['status'] == 'ACTIVE') {
            $data['position_id']  = $position_id;      
            $claimPos = $bitfinexAPI->claim_positions($data);   //close long position 
            
            echo $newline.$claimPos['message'].$newline;

        } 
    }
    
    
    //stop loss 
    if($latestPrice < $ma_30) {
        //stop loss (short pos) is 2% above ATL
        $stopLow = $ATL + $ATL * 0.02;
        
        $output .= $newline.'ATL: '.number_format($ATL, 4).' | stop loss: '.number_format($stopLow, 4).' | ';
        if($latestPrice >= $stopLow) {
            $output .= ' stop loss exit';
            
            if($debug != 1 && $bitfinex_trading == 1) {
                $data['position_id'] = $position_id; 
                $claimPos = $bitfinexAPI->claim_positions($data);
                echo $newline.$claimPos['message'].$newline;
            }                
        }
        else {
            $output .= ' no exit yet';
        }
        $output .= $newline;
    }
}

echo $output;

echo 'active positions: ';
if(is_array($active_pos)) {
    array_debug($active_pos);
}
else {
    echo 'None';
}
?>