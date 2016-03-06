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

function update_last_action($last_action_data) {

    global $db, $context;
    
    $queryD = 'INSERT INTO '.$context['tradeDataTable'].' SET 
        last_action="'.$last_action_data['last_action'].'",
        last_price="'.$last_action_data['last_price'].'",            
        trade_signal="'.$last_action_data['trade_signal'].'",
        last_updated="'.date('Y-m-d H:i:s', time()).'",
        currency="'.$last_action_data['currency'].'",
        exchange="bitfinex"';
    
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

$output .= 'api_options'.$newline;
foreach($optionsArray as $thisOpt) {
    $output .= ' '.$thisOpt.': '.$bitfinexOptions[$thisOpt].' | ';
}
    

$currency = $bitfinexOptions['bitfinex_currency']; //currency defined in api_options
$symbol = strtoupper($currency.'usd'); //currency symbol used for making orders
$rangePercent = $bitfinexOptions['bitfinex_sl_range']; //stop loss range
$bitfinexBalance = $bitfinexOptions['bitfinex_balance']; //% of balance to initially trade
$bitfinex_pd_percent = $bitfinexOptions['bitfinex_pd_percent']; //% trend of pump and dump
$pumpPercent = $bitfinex_pd_percent;
$dumpPercent = -$bitfinex_pd_percent;
        
//database price field
$price_field = 'bitfinex_'.$currency; 

$btcPrice = getBitfinexPrice('btc'); //current market BTC price
$ltcPrice = getBitfinexPrice('ltc'); //current market LTC price

if($currency == 'btc') { //which price to use
    $latestPrice = $btcPrice;
} 
else { //ltc
    $latestPrice = $ltcPrice;
}


if($_GET['latestPrice']) { //test price passed in URL
    $btcPrice = $ltcPrice = $latestPrice = $_GET['latestPrice'];
}

$acctFunds = $bitfinexAPI->get_balances(); 
$acctMargin = $bitfinexAPI->margin_infos(); 

//funds in margin balance
$acctBTC = $acctFunds[4]['amount'];
$acctLTC = $acctFunds[5]['amount'];
$acctUSD = $acctFunds[8]['amount'];

$marginUSD = $acctMargin[0]['margin_limits'][0]['tradable_balance'];

//how much btc/ltc you can buy - determined from margin balance
$tradable['btc'] = number_format($marginUSD/$btcPrice, 4); 
$tradable['ltc'] = number_format($marginUSD/$ltcPrice, 4); 


if($bitfinexTrading == 1) {
    $output .= 'Bitfinex trading is ON';
}
else {
    $output .= 'Bitfinex trading is OFF';
}


$queryD = 'SELECT * FROM '.$context['tradeDataTable'].' ORDER BY last_updated desc LIMIT 1';    
$resultD = $db->query($queryD);

foreach($resultD as $row){
    $last_updated = strtolower($row['last_updated']);
    $trade_signal = strtolower($row['trade_signal']);
    $last_action = strtolower($row['last_action']);
}

$candleData->get_candles($price_field); //get all candle data from db

//get ATH & ATL for stop loss calculations
$ATH = $candleData->get_recorded_ATH(); //ATH = all time high
$ATL = $candleData->get_recorded_ATL(); //ATL = all time low
$range = abs($ATH - $ATL);

//get overall trend for 24 candles (12 hours)
$percentDiff = $candleData->get_percent_diff(); //determine if trend is pump or dump
$percentDiff = number_format($percentDiff, 4);

//get active positions id
$activePos = $bitfinexAPI->active_positions();
$position = $activePos[0]; 

$posAmt = abs($position['amount']); //must be positive #

$tradeAmt = 0.01; //default trade amount is the minimum amt


//get ema data
$ema10 = $candleData->get_ema(10);
$ema21 = $candleData->get_ema(21);

//set the stop losses for different trends
if($percentDiff > $pumpPercent) { //during a pump, the stop loss is the ema-21 line
    $stopHigh = $stopLow = $ema21; 
    $SLType = 'EMA-21';
}
else if($percentDiff < $dumpPercent) { //during a dump, the stop loss is the ema-21 line
    $stopHigh = $stopLow = $ema21; 
    $SLType = 'EMA-21';
}
else { //normal trend    
    $rangePercent = $rangePercent/100; //% of the range bet. ATH and ATL

    $stopHigh = $ATH - ($range * $rangePercent);
    $stopLow = $ATL + ($range * $rangePercent);
    $SLType = 'Range'; 
    
    if($latestPrice < $stopHigh && $latestPrice > $stopLow) { //no trend
        $isNoTrend = 1;
    }
    else if($percentDiff > -1 || $percentDiff < 1) {
        $isNotrend = 1;
    }
    else {
        $isNoTrend = 0;
    }
}

if($ema10 > $ema21) {
    $crossOver = 1;
    $crossUnder = 0;
    $emaTrend = 'Cross Over';
    if($latestPrice > $stopHigh) 
        $uptrend = 1;
    else
        $uptrend = 0;
}
else {
    $crossOver = 0;
    $crossUnder = 1;
    $emaTrend = 'Cross Under';
    if($latestPrice < $stopLow)
        $downtrend = 1;
    else
        $downtrend = 0;
}

$percentDiff_12 = $candleData->get_percent(); //trend of 12 candles
$percentDiff_12 = number_format($percentDiff_12, 2);
if($percentDiff_12 > -1 && $percentDiff_12 < 1) { 
    $uptrend = 0; $downtrend = 0;
}


/*
$position = array(
    'id' => 9999,
    'base' => 433,
    'pl' => 0.12,
    'amount' => -0.02
);*/

if(is_array($position)) { //active position
    $positionID = $position['id'];
    
    if($position['pl'] >= 0.05) { //profiting position
        if($position['pl'] >= 0.10) {
            $tradeAmt = ($marginUSD/$latestPrice) * 0.90; //use the remaining 90% of balance
        }
        else {
            $tradeAmt = $marginUSD/$latestPrice; //how many coins are tradeable
            $tradeAmt = $tradeAmt * $bitfinexBalance/100; //don't use the entire balance
            $tradeAmt = number_format($tradeAmt, 4); //don't need to trade many decimals
        }
        
        $output .= ' | Green Zone';

        if($position['amount'] < 0) { //short
            $newTrade = $bitfinexAPI->margin_short_pos($symbol, $latestPrice, $tradeAmt);
            $action = 'Sell';   
            $trade_signal = 'green';
            if($latestPrice < $position['base']) {
                $stopLow = $position['base'];
                $SLType = 'Base';
            } 
        } 
        else { //long
            $newTrade = $bitfinexAPI->margin_long_pos($symbol, $latestPrice, $tradeAmt);
            $action = 'Buy';    
            $trade_signal = 'green';
            if($latestPrice > $position['base']) {
                $stopHigh = $position['base'];
                $SLType = 'Base';
            } 
        }
    }
    
    if($position['pl'] >= 0.50) { //exit with profits
        if($position['amount'] < 0) { //short
            $newTrade = $bitfinexAPI->margin_long_pos($symbol, $latestPrice, $posAmt);
            $action = 'Buy';
            $trade_signal = 'green_exit';
        } 
        else { //long
            $newTrade = $bitfinexAPI->margin_short_pos($symbol, $latestPrice, $posAmt);
            $action = 'Long';
            $trade_signal = 'green_exit';
        }
    }
}
else { //No positions active
    $position['pl'] = 0; //make sure pl is defined 
}


$stopHigh = number_format($stopHigh, 2);
$stopLow = number_format($stopLow, 2);

$output .= $newline.$newline.'last_updated | '.$last_updated.' | last_action: '.$last_action.' | trade_signal: '.$trade_signal;

$output .= $newline.$newline;
$output .= 'account balance: '.number_format($acctUSD, 2).' '.$newline;
$output .= 'margin: '.number_format($marginUSD, 4).' | ';
$output .= 'tradeable btc: '.$tradable['btc'].' | tradeable ltc: '.$tradable['ltc'].$newline.$newline;
$output .= 'current prices: | btc: '.number_format($btcPrice, 4).' | ltc: '.number_format($ltcPrice, 4).$newline.$newline;

$output .= 'ema-10: '.$ema10.' | ema-21: '.$ema21.' | '.$emaTrend.$newline.$newline;
$output .= 'Recorded ATH: '.$ATH.' | long stop loss: '.$stopHigh.' ('.$SLType.')'; 
$output .= $newline.'Recorded ATL: '.$ATL.' | short stop loss: '.$stopLow.' ('.$SLType.')';

$output .= $newline.$newline.' 12 candle diff | '.$percentDiff_12.'%';
$output .= $newline.' 24 candle diff | '.number_format($percentDiff, 2).'% | ';


if($isNoTrend == 1) { //prevent trading during side trends
    $output .= $trade_signal = 'No trend ';
    $action = 'None';
}
else {
    if($uptrend) { //uptrend
        $output .= $trade_signal = 'Uptrend ';
        $action = 'Buy';

        //default uptrend action - close short pos & open long pos
        if($positionID && $position['amount'] < 0) { //if short pos is active  
            $newTrade = $bitfinexAPI->margin_long_pos($symbol, $latestPrice, $posAmt);      
        }

        if(!isset($positionID)) { //if there is no active position
            $newTrade = $bitfinexAPI->margin_long_pos($symbol, $latestPrice, $tradeAmt);  //new long position    
        }
        else {
            $newTrade = 'Position already open';
        }
    } //uptrend
    else if($downtrend) { //downtrend
        $output .= $trade_signal = 'Downtrend ';
        $action = 'Sell';

        //default action - close long pos & open short pos
        if($positionID && $position['amount'] > 0) { //if long position is active
            $newTrade = $bitfinexAPI->margin_short_pos($symbol, $latestPrice, $posAmt);
        }

        if(!isset($positionID)) { //if there is no active position
            $newTrade = $bitfinexAPI->margin_short_pos($symbol, $latestPrice, $tradeAmt); //new short position
        }
        else {
            $newTrade = 'Position already open';
        }
    } //downtrend
    else {
        $output .= $trade_signal = 'No trend ';
        $action = 'None';
    }
} //else

if($percentDiff > $pumpPercent) { //pump - a constant uptrend for a short burst of time
    $output .= $trade_signal = 'Pumping ';
}
else if($percentDiff < $dumpPercent) { //dump - a constant downtrend for a short burst of time
    $output .= $trade_signal = 'Dumping ';
}

$output .= ' | tradeAmt: '.number_format($tradeAmt, 4).' | posAmt: '.number_format($posAmt, 4).' '.$currency.'';

if($positionID && $position['amount'] > 0) { //if long pos open
    if($latestPrice <= $stopHigh) { //uptrend stop loss
        //close long pos
        $action = 'Sell'; $trade_signal = 'long_sl_exit'; 
        $output .= $SLMsg = ' | Long SL Exit'; 

        $newTrade = $bitfinexAPI->margin_short_pos($symbol, $latestPrice, $posAmt);    
    } //stop loss
}
else if($positionID && $position['amount'] < 0) { //if short pos open
    if($latestPrice >= $stopLow) { //downtrend stop loss
        //close short pos
        $action = 'Buy'; $trade_signal = 'short_sl_exit'; 
        $output .= $SLMsg = ' | Short SL Exit';

        $newTrade = $bitfinexAPI->margin_long_pos($symbol, $latestPrice, $posAmt); 
    } //stop loss 
}

$output .= ' | action: '.$action.' '.$newline.$newline;

if($newTrade['is_live'] == 1) { //new pos opened
    $last_action_data = array(
        'last_action' => strtolower($action),
        'last_price' => $latestPrice,
        'trade_signal' => strtolower($trade_signal),
        'currency' => $currency,
    );
    
    $sendMailBody = $action.' '.$tradeAmt.' '.$currency.' at '.$latestPrice.' '.$SLMsg.' ('.date('h:i A', time()).')';
    
    $candleData->sendMail($sendMailBody);
    update_last_action($last_action_data);    
}

array_debug($newTrade);

echo $output; 

echo $newline.$newline.'Active Positions: ';
if($position['id']) {
    array_debug($activePos);
}
else {
    echo 'None';
}

?>