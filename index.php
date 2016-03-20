<?php
include('include/api_database.php');
include('include/api_bitfinex.php');
include('include/config.php');



function priceRow($pair) {
    
    global $allPrices;
    
    return '<td><a href="https://btc-e.com/exchange/'.$pair.'" target="_blank">'.$allPrices[$pair]['last'].'</a></td>
        <td> '.$allPrices[$pair]['high'].' </td>
        <td> '.$allPrices[$pair]['low'].'</td>';
}


function priceTable($allPrices) {
    echo '<table border="1" class="table">
            <tr>
                <td>Pair</td>
                <td>Last Price</td>
                <td>24h High</td>
                <td>24h Low</td>
            </tr>
            <tr>
                <td>BTC/USD</td>
                '.priceRow('btc_usd').'
            </tr>
            <tr>
                <td>LTC/USD </td>
                '.priceRow('ltc_usd').'
            </tr>
            <tr>
                <td>LTC/BTC </td>
                '.priceRow('ltc_btc').'            
            </tr>
            <tr>
                <td>EUR/USD </td>
                '.priceRow('eur_usd').'            
            </tr>
        </table>';
}


function retrieveJSON($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    $result = curl_exec($ch);
    curl_close($ch);

    $json = json_decode($result, true);
    return $json;
}

//get price for BTC-E currency pair
function getPriceData($pair) {
    global $public_api;
    $json = retrieveJSON($public_api.'ticker/'.$pair);        
    return $json[$pair];
}


$candleData = new Database($db);
$candleData->get_options();
$candleData->get_candles('bitfinex_btc');
$diff = $candleData->get_percent();

$percentDiff_12 = $candleData->get_percent(); //trend of 12 candles (6 hours)
$percentDiff_12 = number_format($percentDiff_12, 2);
//echo $percentDiff_12;


//get ema10 and ema21
$k21 = 2/(21+1); //smoothing constant - 21 day
$k10 = 2/(10+1); //smoothing constant - 10 day

$q = 'SELECT date_format(time, "%m/%d/%Y %h:%i %p") as time, bitfinex_btc from api_prices order by count desc';
$res = $db->query($q);

$array = array();
$arrayPrice = array();
foreach($res as $row) {
    array_push($array, $row);
    array_push($arrayPrice, $row['bitfinex_btc']);
}

$ema10 = $ema21 = 0;
for($count = 1; $count < 70; $count++) {

    $bitfinex_btc = $array[$count]['bitfinex_btc'];
    if($count > 9) {
        $last_10 = array_slice($arrayPrice, $count-10, 10, true);
        $sma10 = array_sum ($last_10)/10;
        
        if($ema10 == 0) $ema10 = $sma10;
        else $ema10 = $k10 * ($bitfinex_btc - $ema10) + $ema10;
        
        if($count > 20) {
            $last_21 = array_slice($arrayPrice, $count-21, 21, true);
            $sma21 = array_sum ($last_21)/21;
            
            if($ema21 == 0) $ema21 = $sma21;
            else $ema21 = $k21 * ($bitfinex_btc - $ema21) + $ema21;
        }
    }

    $sma10 = number_format($sma10, 2);
    $ema10 = number_format($ema10, 2);
    $sma21 = number_format($sma21, 2);
    $ema21 = number_format($ema21, 2);
    
    $emaTable .= '<tr>
        <td>'.$count.'</td>
        <td>'.$array[$count]['time'].'</td>
        <td>'.$array[$count]['bitfinex_btc'].'</td>
        <td title="SMA-10: '.$sma10.'">'.$ema10.'</td>  
        <td title="SMA-21: '.$sma21.'">'.$ema21.'</td>
    </tr>';    
}

$bitfinexEMA = '<table class="table">
<tr>
    <th>count</th>
    <th>day</th>
    <th>price</th>
    <th>10 day ema</th>
    <th>21 day ema</th>
</tr>
'.$emaTable.'
</table>';

global $public_api;
$public_api = 'https://btc-e.com/api/3/';

//BTC-E prices 
$currencyPair = array('btc_usd', 'ltc_usd', 'ltc_btc', 'eur_usd');
 
foreach($currencyPair as $cPair) {
    $allPrices[$cPair] = getPriceData($cPair);
}

//changing the trade options 
if($_POST['submit_options']) {
    $bitfinex_currency = $_POST['bitfinex_currency'];
    $bitfinex_balance = $_POST['bitfinex_balance'];
    $bitfinex_trading = $_POST['bitfinex_trading'];
    $bitfinex_sl_range = $_POST['bitfinex_sl_range'];
    $bitfinex_pd_percent = $_POST['bitfinex_pd_percent'];
    $bitfinex_pl_exit = $_POST['bitfinex_pl_exit'];
    
    //do multiple queries in one call
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, 0);
    
    $queryO = 'UPDATE '.$context['optionsTable'].' SET
            setting = "'.$bitfinex_currency.'" WHERE opt = "bitfinex_currency";
        UPDATE '.$context['optionsTable'].' SET
            setting = "'.$bitfinex_balance.'" WHERE opt = "bitfinex_balance";
        UPDATE '.$context['optionsTable'].' SET
            setting = "'.$bitfinex_trading.'" WHERE opt = "bitfinex_trading";
        UPDATE '.$context['optionsTable'].' SET
            setting = "'.$bitfinex_sl_range.'" WHERE opt = "bitfinex_sl_range";
        UPDATE '.$context['optionsTable'].' SET
            setting = "'.$bitfinex_pd_percent.'" WHERE opt = "bitfinex_pd_percent";
        UPDATE '.$context['optionsTable'].' SET
            setting = "'.$bitfinex_pl_exit.'" WHERE opt = "bitfinex_pl_exit";
    ';
   
    try {
        $db->exec($queryO);
    }
    catch (PDOException $e) {
        echo $e->getMessage();
        die();
    }
}

//get the current options from api_options
$queryO = $db->query('SELECT * FROM '.$context['optionsTable'].' ORDER BY opt');

foreach($queryO as $opt) { 
    if($opt['opt'] == 'bitfinex_trading' || $opt['opt'] == 'bitfinex_currency')
        $bitfinexOption[$opt['opt']][$opt['setting']] = 'selected';
    else
        $bitfinexOption[$opt['opt']] = $opt['setting'];
}

$bitfinex_sl_range = $bitfinexOption['bitfinex_sl_range'];
$bitfinex_balance = $bitfinexOption['bitfinex_balance'];
$bitfinex_pd_percent = $bitfinexOption['bitfinex_pd_percent'];
$bitfinex_pl_exit = $bitfinexOption['bitfinex_pl_exit'];


$bitfinex_trading_dropdown = '<select name="bitfinex_trading">
    <option '.$bitfinexOption['bitfinex_trading'][1].'>1</option>
    <option '.$bitfinexOption['bitfinex_trading'][0].'>0</option>
</select>';

$bitfinex_currency_dropdown = '<select name="bitfinex_currency">
    <option '.$bitfinexOption['bitfinex_currency']['btc'].'>btc</option>
    <option '.$bitfinexOption['bitfinex_currency']['ltc'].'>ltc</option>
</select>';


$price_change = array();
$queryP = $db->query('SELECT *, date_format(time, "%m/%d/%Y %h:%i %p") as time FROM '.$context['pricesTable30m'].' order by count desc'); 

foreach($queryP as $priceRow) { 
    array_push($price_change, $priceRow);
}

$c = 70;
foreach($price_change as $i => $p) {
    
    $exchangeCurrency = array('cex_btc', 'cex_ltc', 'bitfinex_btc', 'bitfinex_ltc');
            
    foreach($exchangeCurrency as $ec) {
        if($price_change[$i-1][$ec] != 0) { //previous price is recorded
           
            $diff[$ec] = ($p[$ec] - $price_change[$i-1][$ec])/$p[$ec]*100;
            $diff[$ec] = number_format($diff[$ec], 2);
            
            if($diff[$ec] > 0) { //positive change
                $diff[$ec] = '<font color="green">(+'.$diff[$ec].'%)</font>';
            }
            else { //negative change
                $diff[$ec] = '<font color="red">('.$diff[$ec].'%)</font>';
            }
        }
    }
   
    $bitfinexHistory .= '<tr>
        <td>'.$p['time'].'</td>
        <td>'.$c.'</td>
        <td>'.number_format($p['bitfinex_btc'], 4).' '.$diff['bitfinex_btc'].'</td>
        <td>'.number_format($p['bitfinex_ltc'], 4).' '.$diff['bitfinex_ltc'].'</td>
    </tr>';
    
    $cexHistory .= '<tr>
        <td>'.$p['time'].'</td>
        <td>'.$c.'</td>
        <td>'.number_format($p['cex_btc'], 4).' '.$diff['cex_btc'].'</td>
        <td>'.number_format($p['cex_ltc'], 4).' '.$diff['cex_ltc'].'</td>
    </tr>';
    
    $c--;
}


$bitfinexHistory = '<table class="table">
    <tr><td>time</td>
    <td>candle #</td>
    <td>bitfinex_btc</td>
    <td>bitfinex_ltc</td>
    </tr>
'.$bitfinexHistory.'</table>';


$cexHistory = '<table class="table">
    <tr><td>time</td>
    <td>candle #</td>
    <td>cex_btc</td>
    <td>cex_ltc</td>
    </tr>
'.$cexHistory.'</table>';



//get data from api_trade_data 
$queryTD = $db->query('SELECT *, date_format(last_updated, "%m/%d/%Y %h:%i %p") as updated_last FROM api_trade_data order by last_updated desc'); 

foreach($queryTD as $td) { 
    $apiTradeData .= '<tr>
        <td>'.$td['exchange'].'</td>
        <td>'.$td['currency'].'</td>
        <td>'.$td['last_action'].'</td>
        <td>'.$td['last_price'].'</td>
        <td>'.$td['trade_signal'].'</td>
        <td>'.$td['updated_last'].'</td></tr>';
}

$apiTradeData = '<table class="table">
    <tr>
        <th>Exchange</th>
        <th>Currency</th>
        <th>last_action</th>
        <th>last_price</th>
        <th>trade_signal</th>
        <th>last_updated</th>
    </tr>'.$apiTradeData.'</table>';


//get daily balance info
$queryB = $db->query('SELECT *, date_format(date, "%m/%d/%Y") as date FROM api_balance order by date desc');

$bCount = 1;
foreach($queryB as $b) {
    $balanceBitfinex = number_format($b['balance_bitfinex'], 2);
    
    if($bCount < 30) {
        $col1 .= '<tr>
        <td>'.$b['date'].'</td>
        <td>$'.$balanceBitfinex.'</td>
        </tr>';
    }
    else {
        $col2 .= '<tr>
        <td>'.$b['date'].'</td>
        <td>$'.$balanceBitfinex.'</td>
        </tr>';

    }
    
    $bCount++;
}

$apiBalance = '<table>
    <tr valign="top">
        <td>
            <table class="table">
                <tr>
                    <td>Date</td>
                    <td>Bitfinex</td>
                </tr>'.$col1.'
            </table>
        </td>
        <td>
            <table class="table">
                <tr>
                    <td>Date</td>
                    <td>Bitfinex</td>
                </tr>'.$col2.'
            </table>
        </td>
    </tr>
</table>';


include('index.html');
?>