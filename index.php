<?php
include('include/config.php');
//include('include/api_btc_e.php');

$array = array();
$queryMA = $db->query('SELECT * FROM '.$context['pricesTable'].' WHERE count <= 60
    order by count desc');
foreach($queryMA as $row) { 
   array_push($array, $row['btce_btc']); 
}


function priceRow($pair) {
    
    global $allPrices;
    
    return '<td> <a href="https://btc-e.com/exchange/'.$pair.'" target="_blank">'.$allPrices[$pair]['last'].'</a> </td>
            <td> '.$allPrices[$pair]['buy'].' / '.$allPrices[$pair]['sell'].' </td>
            <td> '.$allPrices[$pair]['high'].' / '.$allPrices[$pair]['low'].'</td>';
}


function priceTable($allPrices) {
    echo '
        <table border="1" class="table">
            <tr>
                <td>Pair</td>
                <td>Last Price</td>
                <td>Buy/Sell</td>
                <td>High/Low</td>
            </tr>
            <tr>
                <td>BTC/USD</td>
                '.priceRow('btc_usd').'
            </tr>
            <tr>
                <td> LTC/USD </td>
                '.priceRow('ltc_usd').'
            </tr>
            <tr>
                <td> LTC/BTC </td>
                '.priceRow('ltc_btc').'            
            </tr>
            <tr>
                <td> EUR/USD </td>
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

global $public_api;
$public_api = 'https://btc-e.com/api/3/';

//BTC-E prices 
$currencyPair = array('btc_usd', 'ltc_usd', 'ltc_btc', 'eur_usd');
 
foreach($currencyPair as $cPair) {
    $allPrices[$cPair] = getPriceData($cPair);
}

//changing the trade options 
if($_POST['submit_options']) {
    $btc_e_currency = $_POST['btc_e_currency'];
    $btc_e_balance = $_POST['btc_e_balance'];
    $btc_e_trading = $_POST['btc_e_trading'];
    $bitfinex_currency = $_POST['bitfinex_currency'];
    $bitfinex_balance = $_POST['bitfinex_balance'];
    $bitfinex_trading = $_POST['bitfinex_trading'];
    
    //do multiple queries in one call
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, 0);
    
    $queryO = 'UPDATE '.$context['optionsTable'].' SET
            setting = "'.$btc_e_currency.'" WHERE opt = "btc_e_currency";               
        UPDATE '.$context['optionsTable'].' set
            setting = "'.$btc_e_trading.'" WHERE opt = "btc_e_trading";
        UPDATE '.$context['optionsTable'].' set
            setting = "'.$btc_e_balance.'" WHERE opt = "btc_e_balance";
        UPDATE '.$context['optionsTable'].' set
            setting = "'.$bitfinex_currency.'" WHERE opt = "bitfinex_currency";
        UPDATE '.$context['optionsTable'].' set
            setting = "'.$bitfinex_balance.'" WHERE opt = "bitfinex_balance";
        UPDATE '.$context['optionsTable'].' set
            setting = "'.$bitfinex_trading.'" WHERE opt = "bitfinex_trading";
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
    $btc_e_option[$opt['opt']][$opt['setting']] = 'selected';
    $bitfinex_option[$opt['opt']][$opt['setting']] = 'selected';
    
    if($opt['opt'] == 'btc_e_balance') {
        $btc_e_balance = $opt['setting'];
    }
    else if($opt['opt'] == 'bitfinex_balance') {
        $bitfinex_balance = $opt['setting'];
    }
}

//print_r($btc_e_trading_option);//

$btc_e_trading_dropdown = '<select name="btc_e_trading">
    <option '.$btc_e_option['btc_e_trading'][1].'>1</option>
    <option '.$btc_e_option['btc_e_trading'][0].'>0</option>
</select>
';

$btc_e_currency_dropdown = '<select name="btc_e_currency">
    <option '.$btc_e_option['btc_e_currency']['btc'].'>btc</option>
    <option '.$btc_e_option['btc_e_currency']['ltc'].'>ltc</option>
</select>';


$bitfinex_trading_dropdown = '<select name="bitfinex_trading">
    <option '.$bitfinex_option['bitfinex_trading'][1].'>1</option>
    <option '.$bitfinex_option['bitfinex_trading'][0].'>0</option>
</select>';

$bitfinex_currency_dropdown = '<select name="bitfinex_currency">
    <option '.$bitfinex_option['bitfinex_currency']['btc'].'>btc</option>
    <option '.$bitfinex_option['bitfinex_currency']['ltc'].'>ltc</option>
</select>';

$price_change = array();
$queryP = $db->query('SELECT * FROM api_prices order by count desc'); 

foreach($queryP as $priceRow) { 
    array_push($price_change, $priceRow);
}

foreach($price_change as $i => $p) {

    $exchangeCurrency = array('btce_btc', 'btce_ltc', 'bitfinex_btc', 'bitfinex_ltc');
            
    foreach($exchangeCurrency as $ec) {
        if($price_change[$i-1][$ec] != 0) { //previous price is recorded
           
            $diff[$ec] =  ($p[$ec] - $price_change[$i-1][$ec])/$p[$ec]*100;
            $diff[$ec] = number_format($diff[$ec], 2);
            
            if($diff[$ec] > 0) { //positive change
                $diff[$ec] = '<font color="green">(+'.$diff[$ec].'%)</font>';
            }
            else { //negative change
                $diff[$ec] = '<font color="red">('.$diff[$ec].'%)</font>';
            }
        }
    }
    
    $btceHistory .= '<tr>
        <td>'.$p['time'].'</td>
        <td>'.number_format($p['btce_btc'], 4).' '.$diff['btce_btc'].'</td>
        <td>'.number_format($p['btce_ltc'], 4).' '.$diff['btce_ltc'].'</td>
    </tr>';
    
    $bitfinexHistory .= '<tr>
        <td>'.$p['time'].'</td>
        <td>'.number_format($p['bitfinex_btc'], 4).' '.$diff['bitfinex_btc'].'</td>
        <td>'.number_format($p['bitfinex_ltc'], 4).' '.$diff['bitfinex_ltc'].'</td>
    </tr>';
}
 
$btceHistory = '<table class="table">
    <tr><td>time</td>
    <td>bitfinex_btc</td>
    <td>bitfinex_ltc</td>
    </tr>
'.$btceHistory.'</table>';


$bitfinexHistory = '<table class="table">
    <tr><td>time</td>
    <td>bitfinex_btc</td>
    <td>bitfinex_ltc</td>
    </tr>
'.$bitfinexHistory.'</table>';





//get data from api_trade_data 
$queryTD = $db->query('SELECT *, date_format(last_updated, "%m/%d/%Y %h:%i %p") as last_updated FROM api_trade_data order by exchange'); 

foreach($queryTD as $td) { 
    $apiTradeData .= '<tr>
        <td>'.$td['exchange'].'</td>
        <td>'.$td['currency'].'</td>
        <td>'.$td['last_action'].'</td>
        <td>'.$td['last_price'].'</td>
        <td>'.$td['trade_signal'].'</td>
        <td>'.$td['last_updated'].'</td></tr>';
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

foreach($queryB as $b) {
    $balanceBTCE = number_format($b['balance_btce'], 2);
    $balanceBitfinex = number_format($b['balance_bitfinex'], 2);
    
    $combinedTotal = $b['balance_btce'] + $b['balance_bitfinex'];
    $combinedTotal = number_format($combinedTotal);
    $apiBalance .= '<tr>
        <td>'.$b['date'].'</td>
        <td>$'.$b['balance_btce'].'</td>
        <td>$'.$b['balance_bitfinex'].'</td>
        <td>$'.$combinedTotal.'</td>
        </tr>';
}

$apiBalance = '<table class="table">
    <tr>
        <td>Date</td>
        <td>BTCE (USD)</td>
        <td>Bitfinex (USD)</td>
        <td>Total</td>
    </tr>'.$apiBalance.'</table>';


include('index.html');
?>