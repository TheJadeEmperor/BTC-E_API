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

$optionsArray = array('bitfinex_currency', 'bitfinex_balance', 'bitfinex_pd_percent', 'bitfinex_sl_range', 'bitfinex_pl_exit', 'bitfinex_trading');

$output .= 'api_options'.$newline;
foreach($optionsArray as $thisOpt) {
    $output .= ' '.$thisOpt.': '.$bitfinexOptions[$thisOpt].' | ';
}

$currency = $bitfinexOptions['bitfinex_currency']; //currency defined in api_options
$symbol = strtoupper($currency.'usd'); //currency symbol used for making orders
$rangePercent = $bitfinexOptions['bitfinex_sl_range']; //stop loss range
$bitfinexBalance = $bitfinexOptions['bitfinex_balance']; //% of balance to initially trade
$bitfinexPLExit = $bitfinexOptions['bitfinex_pl_exit']; //when to exit with profits

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

$latestPrice = number_format($latestPrice, 4);

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
    $last_price = strtolower($row['last_price']);
}

$candleData->get_candles($price_field); //get all candle data from db

//get ATH & ATL for stop loss calculations
$ATH = $candleData->get_recorded_ATH(); //ATH = all time high
$ATL = $candleData->get_recorded_ATL(); //ATL = all time low
$range = abs($ATH - $ATL);


$percentDiff_12 = $candleData->get_percent_diff(); //trend of 12 candles (24 hrs)
$percentDiff_12 = number_format($percentDiff_12, 2);


//get active positions id
$activePos = $bitfinexAPI->active_positions();
$position = $activePos[0]; 
$posAmt = abs($position['amount']); //must be positive #

$tradeAmt = 0.01; //default trade amount is the minimum amt


//get ema data
$ema10 = $candleData->get_ema(10);
$ema21 = $candleData->get_ema(21);


//set the stop losses for different trends
if($percentDiff_12 > $pumpPercent) { //during a pump/dump, the stop loss is the ema-21 line
    $stopHigh = $stopLow = $ema21; 
    $SLType = 'EMA-21';
    $trend_signal = ' Pumping ';
}
else if($percentDiff_12 < $dumpPercent) { //during a pump/dump, the stop loss is the ema-21 line
    $stopHigh = $stopLow = $ema21; 
    $SLType = 'EMA-21';
    $trend_signal = ' Dumping ';
}
else if($percentDiff_12 > -1.0 && $percentDiff_12 < 1.0) { //No trend or side trend
    $isSideTrend = 1; //no trend
    
    $stopHigh = $ATH;
    $stopLow = $ATL;
    $SLType = 'Range'; 
} //-1 < X < 1
else { //normal trend    
    $isSideTrend = 0;

    $stopHigh = $ATH;
    $stopLow = $ATL;
    $SLType = 'Range'; 
}

if($ema10 > $ema21) {
    $crossOver = 1;
    $crossUnder = 0;
    $emaTrend = 'Cross Over';
    if($latestPrice > $ATH) 
        $uptrend = 1;
    else
        $uptrend = 0;
}
else {
    $crossOver = 0;
    $crossUnder = 1;
    $emaTrend = 'Cross Under';
    if($latestPrice < $ATL)
        $downtrend = 1;
    else
        $downtrend = 0;
}

/*
$position = array(
    'id' => 9999,
    'base' => 411,
    'pl' => 0.05,
    'amount' => -0.02
);*/


$posExit = 0; //exit a position | 0 = false | 1 = true
if(is_array($position)) { //active position
    $positionID = $position['id'];
    
    //if no profit
    if($position['pl'] < 0) {
    
        //check for 3 candles - if no profit, then exit
        echo ' '.$position['timestamp'].' | '.date('Y-m-d H:i:s', $position['timestamp']).' | ';

        //3 candles = 6 hours
        $posTime6Hours = strtotime("+6 hours", $position['timestamp']);
        
        if(time() > $posTime6Hours) { //6 hours is up
            $posExit = 1; $trade_signal = '6_hour_limit_exit';
        }
    }
    
    if($position['pl'] >= $bitfinexPLExit) { //bitfinexPLExit set in database
        $trade_signal = 'green_exit';
        $posExit = 1; //exit when profits hit exit level
    }
    else {
        
        if($position['pl'] >= 0.025) { //profiting position
            
            if($last_action == 'bitfinex_sl_exit') { //SL is recorded
                echo ' $trade_signal: '.$trade_signal;
                $stopLow = $stopHigh = $last_price; //last_price from api_trade_data
            } 
            else { //no SL recorded
                $stopLow = $stopHigh = $position['base'];
            }
            
            $SLType = 'Base';
            //new stop losses
            if($position['amount'] < 0) { //short
                if($latestPrice < $position['base']) {    
                    $trade_signal = 'short_sl_exit';
                    $stopLow = $latestPrice;
                } 
            }
            else { //long
                if($latestPrice > $position['base']) {   
                    $trade_signal = 'long_sl_exit';
                    $stopHigh = $latestPrice;
                } 
            } 
            
            $last_action_data = array(
                'last_action' => 'bitfinex_sl_exit',
                'last_price' => $position['base'],
                'trade_signal' => $trade_signal,
                'currency' => $currency,
            );
    
            $candleData->update_last_action($last_action_data);    
            
            $tradeAmt = $marginUSD/$latestPrice; //how many coins are tradeable
            $tradeAmt = $tradeAmt * $bitfinexBalance/100; //don't use the entire balance
            $tradeAmt = number_format($tradeAmt, 4); //don't need to trade many decimals
            $output .= ' | Green Zone';

            if($isSideTrend == 0) { //if there is a trend, trade more
                $trade_signal = 'green';
                
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
$output .= 'ema-10: '.$ema10.' | ema-21: '.$ema21.' | '.$emaTrend.$newline.$newline;

$output .= 'current prices: | btc: '.number_format($btcPrice, 4).' | ltc: '.number_format($ltcPrice, 4).$newline.$newline;

$output .= 'Recorded ATH: '.$ATH.' | long stop loss: '.$stopHigh.' ('.$SLType.')'; 
$output .= $newline.'Recorded ATL: '.$ATL.' | short stop loss: '.$stopLow.' ('.$SLType.')';

$trade_signal = 'n/a';
if($isSideTrend == 1) { //prevent trading during side trends
    $trend_signal = 'No trend ';
    $action = 'None';
}
else {
    if($uptrend) { //uptrend
        $trend_signal = ' Uptrend ';
        $action = 'Buy';

        if(!isset($positionID)) { //if there is no active position
            $newTrade = $bitfinexAPI->margin_long_pos($symbol, $latestPrice, $tradeAmt);  //new long position    
        }
        else {
            $newTrade = 'Position already open';
        }
    } //uptrend
    else if($downtrend) { //downtrend
        $trend_signal = ' Downtrend ';
        $action = 'Sell';

        if(!isset($positionID)) { //if there is no active position
            $newTrade = $bitfinexAPI->margin_short_pos($symbol, $latestPrice, $tradeAmt); //new short position
        }
        else {
            $newTrade = 'Position already open';
        }
    } //downtrend
    else {
        $trend_signal = 'No trend ';
        $action = 'None';
    }
} //else


if($positionID && $position['amount'] > 0) { //if long pos open
    if($latestPrice <= $stopHigh) { //uptrend stop loss
        $trade_signal = 'long_sl_exit';  //close long pos
        $output .= $SLMsg = ' | Long SL Exit'; 
        $posExit = 1;  
    } //stop loss
}
else if($positionID && $position['amount'] < 0) { //if short pos open
    if($latestPrice >= $stopLow) { //downtrend stop loss   
        $trade_signal = 'short_sl_exit';  //close short pos
        $output .= $SLMsg = ' | Short SL Exit';
        $posExit = 1;
    } //stop loss 
}


$output .= $newline.$newline.' 12 candle diff: '.$percentDiff_12.'% | '.$trend_signal.' | '.$trade_signal.' | tradeAmt: '.number_format($tradeAmt, 4).' | posAmt: '.number_format($posAmt, 4).' '.$currency.'';


if($positionID && $posExit == 1) { //exit position
    
    if($position['amount'] < 0) { //short exit
        $newTrade = $bitfinexAPI->margin_long_pos($symbol, $latestPrice, $posAmt);
        $action = 'Buy';
    } 
    else { //long exit 
        $newTrade = $bitfinexAPI->margin_short_pos($symbol, $latestPrice, $posAmt);
        $action = 'Sell';
        
    }
} //exit position

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
    $candleData->update_last_action($last_action_data);    
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