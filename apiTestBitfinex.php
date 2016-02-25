<?php
function array_debug($array) {
    echo '<pre>';
    print_r($array);
    echo '</pre>';
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
    
    $resultD = $db->query($queryD);
}


include_once('include/api_bitfinex.php');
include_once('include/api_database.php');
include_once('include/config.php');


global $totalBalance; //total balance in your account
global $bitfinexAPI; //bitfinex object
global $rangePercent; //stop loss range %
global $pumpPercent; 
global $dumpPercent; 
global $bitfinexBalance;
$pumpPercent = 3.8;
$dumpPercent = -3.8;

$debug = $_GET['debug'];
$bitfinexAPI = new Bitfinex(BITFINEX_API_KEY, BITFINEX_API_SECRET);
$candleData = new Database($db);

if($debug == 1) { //debug mode - no trading, output only
    echo '<< debug mode >>'; $newline = '<br>';
}
else { //live mode - output is sent to email - \n is newline in emails
    $newline = "\n";
}


$bitfinexOptions = $candleData->get_options();
$bitfinexTrading = $bitfinexOptions['bitfinex_trading'];
$bitfinexAPI->isBitfinexTrading($bitfinexTrading); 

$optionsArray = array('bitfinex_currency', 'bitfinex_balance', 'bitfinex_pd_percent', 'bitfinex_sl_range', 'bitfinex_trading');

$output .= $newline.$newline.'api_options'.$newline;
foreach($optionsArray as $thisOpt) {
    $output .= ' '.$thisOpt.': '.$bitfinexOptions[$thisOpt].' | ';
}
    
if($bitfinexTrading == 1) {
    $output .= 'Bitfinex trading is ON';
}
else {
    $output .= 'Bitfinex trading is OFF';
}

$currency = $bitfinexOptions['bitfinex_currency']; //currency defined in api_options
$symbol = strtoupper($currency.'usd'); //currency symbol used for making orders
$rangePercent = $bitfinexOptions['bitfinex_sl_range']; //stop loss range
$bitfinexBalance = $bitfinexOptions['bitfinex_balance']; //% of balance to initially trade

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


//funds in margin balance
$acctBTC = $acctFunds[4]['amount'];
$acctLTC = $acctFunds[5]['amount'];
$acctUSD = $acctFunds[8]['amount'];

$marginUSD = $acctMargin[0]['margin_limits'][0]['tradable_balance'];

//how much btc/ltc you can buy - determined from margin balance
$tradable['btc'] = number_format($marginUSD/$btcPrice, 4); 
$tradable['ltc'] = number_format($marginUSD/$ltcPrice, 4); 

$output .= $newline.$newline;
$output .= 'account balance: '.number_format($acctUSD, 2).' '.$newline;
$output .= 'margin: '.number_format($marginUSD, 4).' | ';
$output .= 'tradeable btc: '.$tradable['btc'].' | tradeable ltc: '.$tradable['ltc'].$newline.$newline;
$output .= 'current prices: | btc: '.number_format($btcPrice, 4).' | ltc: '.number_format($ltcPrice, 4).$newline.$newline;


//get api_trade_data
$queryT = 'SELECT * FROM '.$context['tradeDataTable'].' WHERE currency = "'.$currency.'"
    AND exchange = "bitfinex"';
$resT = $db->query($queryT);

foreach($resT as $t) { 
    $last_updated = $t['last_updated'];
    $trade_signal = $t['trade_signal'];
}

$candleData->get_candles($price_field); //get all candle data from db


//get ema data
$ema10 = $candleData->get_ema(10);
$ema21 = $candleData->get_ema(21);
if($ema10 > $ema21) {
    $crossOver = 1;
    $crossUnder = 0;
    $emaTrend = 'Cross Over';
}
else {
    $crossOver = 0;
    $crossUnder = 1;
    $emaTrend = 'Cross Under';
}


//get ATH & ATL for stop loss calculations
$recordedATH = $candleData->get_recorded_ATH(); //ATH = all time high
$recordedATL = $candleData->get_recorded_ATL(); //ATL = all time low

//adjust the ATH and ATL by 0.5%
$ATH = $recordedATH + $recordedATH * 0.005;
$ATL = $recordedATL - $recordedATL * 0.005;

//get overall trend for 24 candles (12 hours)
$percentDiff = $candleData->get_percent_diff(); //determine if trend is pump or dump
$percentDiff = number_format($percentDiff, 4);

//get active positions id
$activePos = $bitfinexAPI->active_positions();
$position = $activePos[0]; 

$posAmt = abs($position['amount']); //must be positive #
$posAmt = number_format($posAmt, 4); //don't need to trade many decimals

$tradeAmt = $marginUSD/$latestPrice; //how many coins are tradeable
$tradeAmt = $tradeAmt * $bitfinexBalance/100; //don't use the entire balance
$tradeAmt = number_format($tradeAmt, 4); //don't need to trade many decimals
/*
$position = array(
    'amount' => '-2.0',
    'base' => '430',
    'id' => '999'
);*/


if(is_array($position)) { //active position
    $positionID = $position['id'];
    
    if($position['pl'] >= 0.05) {
        $tradeAmt = ($marginUSD/$latestPrice) * 0.95; //use the remaining balance
    }
}
else { //No positions active
    $position['pl'] = 0; //make sure pl is defined 
}


if($positionID) { //if there is an active position
    //if profiting, trade the rest of the balance
    if($position['pl'] > 0.05) {
        $output .= ' | green zone | ';

        if($position['amount'] < 0) { //short
            $newTrade = $bitfinexAPI->margin_short_pos($symbol, $latestPrice, $tradeAmt);
            $action = 'Sell';
        } 
        else { //long
            $newTrade = $bitfinexAPI->margin_long_pos($symbol, $latestPrice, $tradeAmt);
            $action = 'Buy';
        }   
    }
}

//set the stop losses for different trends
if($percentDiff > $pumpPercent) { //during a pump, the stop loss is the ema-21 line
    $stopHigh = $ema21; 
    $stopLow = $ema21;
    $SLType = 'EMA-21';
}
else if($percentDiff < $dumpPercent) { //during a dump, the stop loss is the ema-21 line
    $stopHigh = $ema21; 
    $stopLow = $ema21;
    $SLType = 'EMA-21';
}
else { //normal trend
    $rangePercent = $rangePercent/100; //% of the range bet. ATH and ATL

    $stopHigh = $ATH - $ATH * (abs($percentDiff) * $rangePercent)/100;
    $stopLow = $ATL + $ATL * (abs($percentDiff)* $rangePercent)/100;
    $SLType = 'Range'; 
}

if($position['amount'] > 0) { //long
    if($position['base'] > $stopHigh) { //default stop loss is position's base
        
        $stopHigh = $position['base'] - 0.01;
        $SLType = 'Base';
    }
}
else if ($position['amount'] < 0) { //short
    if($position['base'] < $stopLow) { //default stop loss is position's base
        
        $stopLow = $position['base'] + 0.01;
        $SLType = 'Base';
    }
}

$stopHigh = number_format($stopHigh, 2);
$stopLow = number_format($stopLow, 2);


$output .= ' ema-10: '.$ema10.' | ema-21: '.$ema21.' | '.$emaTrend.$newline.$newline;

$output .= 'Recorded ATH: '.$recordedATH.' | Adjusted ATH: '.number_format($ATH, 2).' | long stop loss: '.$stopHigh.' ('.$SLType.')'; 
$output .= $newline.'Recorded ATL: '.number_format($recordedATL, 2).' | Adjusted ATL: '.number_format($ATL, 2).' | short stop loss: '.$stopLow.' ('.$SLType.')';

$output .= $newline.$newline.' 24 candle trend | '.number_format($percentDiff, 2).'% | ';


if($latestPrice > $ATH && $crossOver == 1) { //uptrend - price is above ATH
    $output .= $trade_signal = 'Uptrend ';
    
    //default uptrend action - close short pos & open long pos
    if($positionID && $position['amount'] < 0) { //if short pos is active
        if($marginUSD > $acctUSD) {
            $newTrade = $bitfinexAPI->margin_long_pos($symbol, $latestPrice, $posAmt); 
        } 
        else {
            $output .= ' no balance to trade ';
        } 
    }

    if($marginUSD > $acctUSD) {
        $newTrade = $bitfinexAPI->margin_long_pos($symbol, $latestPrice, $tradeAmt);  //new long position
    }

} //uptrend
else if($latestPrice < $ATL && $crossUnder == 1) { //downtrend - price is below ATL

    $output .= $trade_signal = 'Downtrend ';
    $action = 'Sell';
    
    //default action - close long pos & open short pos
    if($positionID && $position['amount'] > 0) { //if long position is active
        if($marginUSD > $acctUSD) {
            $newTrade = $bitfinexAPI->margin_short_pos($symbol, $latestPrice, $posAmt);
        } 
        else { 
            $output .= ' no balance to trade '; 
        }
    }
 
    if($marginUSD > $acctUSD) {
        $newTrade = $bitfinexAPI->margin_short_pos($symbol, $latestPrice, $tradeAmt); //new short position
    }
 
} //downtrend
else { //not uptrend or downtrend
    
    if($percentDiff > 4) { //pump - a constant uptrend for a short burst of time
        $output .= $trade_signal = 'Pumping ';
    }
    else if($percentDiff < -4) { //dump - a constant downtrend for a short burst of time
        $output .= $trade_signal = 'Dumping ';
    }
    else { //default is no trend
        $output .= $trade_signal = 'No trend ';
    }
}

$output .= ' | tradeAmt: '.number_format($tradeAmt, 4).' '.$currency.'';

if($positionID && $position['amount'] > 0) { //if long pos open
    if($latestPrice <= $stopHigh) { //uptrend stop loss
        //close long pos
        $action = $trade_signal = 'Sell'; 
        $output .= ' | long SL exit'; 

        $newTrade = $bitfinexAPI->margin_short_pos($symbol, $latestPrice, $posAmt);
    } //stop loss
}
else if($positionID && $position['amount'] < 0) { //if short pos open
    if($latestPrice >= $stopLow) { //downtrend stop loss
        //close short pos
        $action = $trade_signal = 'Buy';
        $output .= ' | short SL exit';

        $newTrade = $bitfinexAPI->margin_long_pos($symbol, $latestPrice, $posAmt); 
    } //stop loss 
}


if($newTrade['is_live'] == 1) {   
    $last_action_data = array(
        'last_action' => strtolower($action),
        'last_price' => $latestPrice,
        'trade_signal' => $trade_signal,
        'currency' => $currency,
    );
    
    $sendMailBody = $action.' '.$tradeAmt.' '.$currency.' at '.$latestPrice.' ('.date('h:i A', time()).')';
    
    $candleData->sendMail($sendMailBody);
    update_last_action($last_action_data);

    $output .= $newline.array_debug($newTrade).$newline;
}

echo $output;


echo $newline.$newline.'Active Positions: ';
if($position['id']) {
    array_debug($activePos);
}
else {
    echo 'None';
}

?>