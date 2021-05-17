<?php
$dir = '../include/';
include($dir.'functions.php');
include($dir.'config.php');
include($dir.'api_bittrex.php');

//set timezone
date_default_timezone_set('America/New_York');

if($_GET['accessKey'] != $accessKey) {
	echo "Wrong access key"; exit;
}
	
$bittrex = new Client ($bittrex_api_key, $bittrex_api_secret);


try {
    $balance = $bittrex->getBalances();
}
catch(Exception $e){
    echo 'Error occurred : '.$e->getMessage(); 
}	

    ?>
    <table class="table">
        <thead class="thead-default">
        <tr>
            <th colspan="8"><a href="https://bittrex.com/Market/Index?MarketName=USDT-ADA" target="_BLANK">Bittrex Balance</a> <img src="include/images/refresh.png" class="clickable" onclick="javascript:reloadBtrexBalance()" width="25px" /> </th>
        </tr>
        <tr>
            <th>Currency</th><th>Balance</th><th>Price</th><th>BTC Value</th><th>USDT</th>
        </tr>
    <?
    
    if(!empty($balance)) { 
        foreach($balance as $key => $val) {
        
            $currency = $val->Currency; //coin 
            $currencyBalance = $val->Balance; //coin balance

            $btcTicker = $bittrex->getTicker('USDT-BTC');
            $btcPrice = $btcTicker->Last;
            
            if($currency == 'BTXCRD') continue; //invalid market
            if($currency == 'BTC' ) {
                $currencyPair = 'USDT-BTC';
                $lastFormat = $bittrex_btc_usd_raw; //formatted price
                $btcValue = $currencyBalance; //btc value
                $usdtValue = $btcValue * $btcPrice; //get usdt value
            }
            else if ($currency == 'USDT') { 
                $lastFormat = 1; //usdt has no price
                $usdtValue = $currencyBalance; 
                $btcValue = $usdtValue / $btcPrice;
            }
            else { //all other coins 
                $currencyPair = 'USDT-'.$currency; //ticker pair
                $coinTicker = $bittrex->getTicker($currencyPair);

                $lastFormat = $coinTicker->Last;
                $usdtValue =  $currencyBalance * $lastFormat; //get usdt value
                $btcValue = $usdtValue / $btcPrice;
            }
        
            $btcValueFormat = number_format($btcValue, 4);
            $usdtValueFormat = number_format($usdtValue, 2);
            $totalBTC += $btcValue;
            $totalUSD += $usdtValue;
            
            //echo $currency.' '.$currencyBalance.' '.$usdtValue.' '.$ticker->Last.' '.$bittrex_btc_usd_raw.' <br />';
            
            if($btcValue > 0.01 || $usdtValue > 1) {
                echo '<tr><td>'.$currency.'</td>
                <td>'.$currencyBalance.'</td>
                <td>'.$lastFormat.'</td>
                <td>'.$btcValueFormat.'</td>
                <td><span style="color: gray">'.$usdtValueFormat.'</span></td>
                </tr>';

            } //if($btcValue > 0.01 || $usdtValue > 1)
        } //foreach($balance as $x => $val)
         
        $totalBTCFormat	= number_format($totalBTC, 4);
        $totalUSDTFormat = number_format($totalUSD, 2);
         
        echo '<tr><td colspan="10">Total BTC: '.$totalBTCFormat.' &nbsp;&nbsp; 
        <span style="color: gray">Total USDT: $'.$totalUSDTFormat.'</span></td>';
    
        echo '</table>';
    } //if(!empty($balance))

?>