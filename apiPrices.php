<?
require_once('btce-api.php');
include('include/mysql.php');
include('include/ez_sql_core.php');
include('include/ez_sql_mysql.php');
include('include/config.php');

function priceRow($pair) {
    
    global $allPrices;
    
    return '<td> <a href="https://btc-e.com/exchange/'.$pair.'" target="_blank">'.$allPrices[$pair]['lastPrice'].'</a> </td>
            <td> '.$allPrices[$pair]['buyPrice'].' / '.$allPrices[$pair]['sellPrice'].' </td>
            <td> '.$allPrices[$pair]['highPrice'].' / '.$allPrices[$pair]['lowPrice'].'</td>';
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
                <td> NVC/USD </td>
                '.priceRow('nvc_usd').'
            </tr>
            <tr>
                <td> EUR/USD </td>
                '.priceRow('eur_usd').'            
            </tr>
        </table>';
}


function accountData($acctInfo) {
    echo $acctFunds = $acctInfo['return']['funds'];

    
    var_dump($acctInfo);
}


$context['tradesTable'] = 'api_trades';
$context['balanceTable'] = 'api_balance';

$api = new BTCeAPI(
    /*API KEY:    */    'P4ZM898V-DJSR3WKJ-CFVK9VKM-5ISFP5KO-ZVQLXW1P', 
    /*API SECRET: */    'bd6c1c022c334572691b57681c71a4734f08e5d2cddf1de3a8481e6be10d76df'
);

global $allPrices;

$allPrices = array(); 

$currencyPair = array('btc_usd', 'ltc_usd', 'ltc_btc', 'nvc_usd', 'eur_usd');
 
foreach($currencyPair as $cPair) {
    $allPrices[$cPair]['lastPrice'] = $api->getLastPrice($cPair);
    $allPrices[$cPair]['buyPrice'] = $api->getBuyPrice($cPair);
    $allPrices[$cPair]['sellPrice'] = $api->getSellPrice($cPair);
    $allPrices[$cPair]['highPrice'] = $api->getHighPrice($cPair);
    $allPrices[$cPair]['lowPrice'] = $api->getLowPrice($cPair);
}

$acctInfo = $api->apiQuery('getInfo');

?>