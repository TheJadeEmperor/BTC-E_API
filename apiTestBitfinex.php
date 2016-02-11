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
$output .= 'current prices: '.$newline.'btc: '.number_format($btcPrice, 4).' | ltc: '.number_format($ltcPrice, 4).$newline;
$output .= 'account balance: '.number_format($acctUSD, 2).' '.$newline;
$output .= 'margin: '.number_format($marginUSD, 4).' | ';
$output .= 'tradeable btc: '.$tradable['btc'].' | tradeable ltc: '.$tradable['ltc'].$newline.$newline;


$bitfinex_trading = $bitfinex_option['bitfinex_trading'];
if($bitfinex_trading == 1) {
    $output .= 'Bitfinex trading is ON';
}
else {
    $output .= 'Bitfinex trading is OFF';
}
$output .= $newline;


//get api_trade_data
$queryT = 'SELECT * FROM '.$context['tradeDataTable'].' WHERE currency = "'.$currency.'"
    AND exchange = "bitfinex"';
$resT = $db->query($queryT);

foreach($resT as $t) { 
    $last_updated = $t['last_updated'];
    $trade_signal = $t['trade_signal'];
}

//echo $last_updated.' '.$trade_signal;

//get ATH & ATL for stop loss calculations
$queryATH = 'SELECT (MAX('.$price_field.')) AS ATH, (MIN('.$price_field.')) AS ATL FROM '.$context['pricesTable30m'].'';
$resultATH = $db->query($queryATH);
        
foreach($resultATH as $row) { 
    $ATH = $row['ATH']; //ATH = all time high 
    $ATL = $row['ATL']; //ATL = all time low
}
//echo 'ath: '.$ATH;


//get active positions id
$activePos = $bitfinexAPI->active_positions();
$position = $activePos[0];
$positionID = $position['id'];

if(!is_array($position)) {
    $position['pl'] = 0; //make sure pl is defined 
}
 //echo 'pl '.$position['pl'];


if($position['id']) { //if there is an active position, the trade amount is the position's amount
    $posAmt = abs($position['amount']); 
}
//trade amount is the margin divided by the latestPrice 
$tradeAmt = $marginUSD/$latestPrice;
$tradeAmt = $tradeAmt * 0.95; //don't use the entire balance

    
$longPosA = array( 
    'symbol' => $symbol,
    'price' => $latestPrice,
    'amount' => "$posAmt",
    'side' => 'buy', //buying side
    'type' => 'limit', //margin buying
    'exchange' => 'bitfinex'
);

$longPosB = array( 
    'symbol' => $symbol,
    'price' => $latestPrice,
    'amount' => "$tradeAmt",
    'side' => 'buy', //buying side
    'type' => 'limit', //margin buying
    'exchange' => 'bitfinex'
);


$shortPosB = array( 
    'symbol' => $symbol,
    'price' => $latestPrice,
    'amount' => "$tradeAmt",
    'side' => 'sell', //buying side
    'type' => 'limit', //margin buying
    'exchange' => 'bitfinex'
);

$shortPosA = array( 
    'symbol' => $symbol,
    'price' => $latestPrice,
    'amount' => "$posAmt",
    'side' => 'sell', //buying side
    'type' => 'limit', //margin buying
    'exchange' => 'bitfinex'
);

$claimData = array(
    'position_id' => $positionID);

//stop loss - 1% below ATH
$stopHigh = $ATH - $ATH * 0.01;

//stop loss - 1% above ATL
$stopLow = $ATL + $ATL * 0.01;


//during a sideways trend, movement is low
if($stopLow > $stopHigh) {
    $stopLowNew = $ATL + $ATL * 0.001;
    $stopHighNew = $ATH - $ATH * 0.001;
}

$output .= $newline.'ATH: '.number_format($ATH, 4).' | stop loss: '.number_format($stopHigh, 4).' | new stop loss: '.$stopHighNew;
$output .= $newline.'ATL: '.number_format($ATL, 4).' | stop loss: '.number_format($stopLow, 4).' |  new stop loss: '. $stopLowNew;

if($stopLowNew > 0) {
    $stopLow = $stopLowNew;
    $stopHigh = $stopHighNew;
}

if($latestPrice > $ATH) { //uptrend
    $output .= '[ uptrend ][ tradeAmt: '.$tradeAmt.' ][ '.$currency.' ]';
    
    //default uptrend action - close short pos & open long pos 
    $action = 'Buy'; 
    $trade_signal = 'uptrend';
     
    if($positionID && $position['amount'] < 0) {
        if($marginUSD > $acctUSD) {
            $newTrade = $bitfinexAPI->new_order($longPosA);
            $claimPos = $bitfinexAPI->claim_positions($claimData);
            print_r($claimPos);  
        } 
        else {
            $output .= ' no balance to trade ';
        } 
    }

    $newTrade = $bitfinexAPI->new_order($longPosB); //new short position
    print_r($newTrade);
      
} //uptrend
else if($latestPrice < $ATL) { //downtrend

    $output .= '[ downtrend ][ tradeAmt: '.$tradeAmt.' ][ '.$currency.' ]';
    
    //default action - close long pos & open short pos
    $action = 'Sell';
    $trade_signal = 'downtrend';
    if($positionID && $position['amount'] > 0) { //if long position is active
        if($debug != 1 && $bitfinex_trading == 1) {
            if($marginUSD > $acctUSD) {
                $newTrade = $bitfinexAPI->new_order($shortPosA);
                $claimPos = $bitfinexAPI->claim_positions($claimData);
                print_r($claimPos);
            } 
            else { 
                $output .= ' no balance to trade '; 
            }
        }
    }

    if($debug != 1 && $bitfinex_trading == 1){
        $newTrade = $bitfinexAPI->new_order($shortPosB); //new short position
        print_r($newTrade);
    }
    
} //downtrend
else {
    $output .= $newline.' no trend '.$newline;
}


if($position['pl'] < -0.03)  {
    $output .= ' force close position | ';
    //if losing profits, then close pos
    if($debug != 1 && $bitfinex_trading == 1) {

        if($posAmt < 0) {
            $newTrade = $bitfinexAPI->new_order($longPosA);
        }
        else {
            $newTrade = $bitfinexAPI->new_order($shortPosA);
        }
    }    
}

if($positionID && $position['amount'] > 0) //if long pos open
if($latestPrice <= $stopHigh) { //uptrend stop loss
    //close long pos
    $action = 'Sell'; 
    $trade_signal = 'uptrend';
    $output .= ' high stop loss exit';
      
    if($debug != 1 && $bitfinex_trading == 1) {       
        $newTrade = $bitfinexAPI->new_order($shortPosA);
        $claimPos = $bitfinexAPI->claim_positions($claimData);
        print_r($claimPos);  
    }

} //stop loss


if($positionID && $position['amount'] < 0) //if short pos open
if($latestPrice >= $stopLow) { //downtrend stop loss
    //close short pos
    $action = 'Buy';
    $trade_signal = 'downtrend';
    $output .= ' low stop loss exit';

    if($debug != 1 && $bitfinex_trading == 1) {
        $newTrade = $bitfinexAPI->new_order($longPosA);
        $claimPos = $bitfinexAPI->claim_positions($claimData);
        print_r($claimPos);
    }
} //stop loss 


if($newTrade['is_live'] == 1) {
    
    $last_action_data = array(
        'last_action' => strtolower($action),
        'last_price' => $latestPrice,
        'trade_signal' => 'downtrend',
        'currency' => $currency,
    );
    
    sendMail('[Bitfinex] '.$action.' '.$tradeAmt.' '.$currency.' at '.$latestPrice);
    update_last_action($last_action_data);

    $output .= '[ sell ]'.$newline;
    $output .= array_debug($newTrade).$newline;
}

echo $output;


echo $newline.'active positions: ';
if(is_array($activePos)) {
    array_debug($activePos);
}
else {
    echo 'None';
}


?>