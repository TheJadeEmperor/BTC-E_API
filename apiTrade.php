<?
include('apiPrices.php');

$acctInfo = $api->apiQuery('getInfo');
$acctFunds = $acctInfo['return']['funds'];


function whichCurrency($pair) {
    switch($pair) {
        case 'btc_usd':
            $currency = 'btc';
            break;
        case 'ltc_usd':
            $currency = 'ltc';
            break;
        case 'nvc_usd':
            $currency = 'nvc';
            break;
        case 'nmc_usd':
            $currency = 'nmc';
            break;
        default:
            $currency = 'usd';
    }
    
    return $currency;
}

function sendMail($sendEmailBody) {
    $headers = 'From: alerts@bestpayingsites.com' . "\r\n" .
    'Reply-To: alerts@bestpayingsites.com' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

    $emailTo = '17182136574@tmomail.net';
    $mailSent = mail($emailTo, 'Text alert', $sendEmailBody, $headers);
    
    if($mailSent) {
        $subject = 'Text alert sent';
    }
    else {
        $subject = 'Text alert NOT sent';
    }
    
    $emailTo = 'louie.benjamin@gmail.com'; 
    echo mail($emailTo, $subject, $sendEmailBody, $headers);
}



$sel = 'SELECT * FROM '.$context['tradesTable'].' WHERE status="A" order by id';
$res = mysql_query($sel, $conn) or die(mysql_error());

while($t = mysql_fetch_assoc($res)) {
    $action = $t['action'];
    $pair = $t['pair'];
    $price = $t['price']; 
    $amount = $t['amount'];
    
    $currency = whichCurrency($pair); 

//echo 'buyAmt ';
    $buyAmt = $sellAmt = $amount;
            
    foreach($allPrices as $cPair => $priceType) {
        if($pair == $cPair) {
            echo $pair.' '; 
            
            if($action == 'buy') {
                echo '[buyAmt '.$buyAmt.'] [target price: <= '.$price.'] [buy price: '.$priceType['buyPrice'].'] ';
                
                if($priceType['buyPrice'] <= $price) {
                    echo ' buy - true ';
                    //make trade
                    try {
                        $tradeResult = $api->makeOrder($buyAmt, $pair, BTCeAPI::DIRECTION_BUY, $price);  
                    }
                    catch(BTCeAPIInvalidParameterException $e) {
                        echo $e->getMessage();
                    } catch(BTCeAPIException $e) {
                        echo $e->getMessage();
                    }
                    
                    var_dump($tradeResult);
                    
                    $msg = 'Trade successful: Buy '.$buyAmt.' of '.$currency.' at price '.$price;
                    
                    if ($tradeResult['success'] == 1) {
                        sendMail($msg);
                    };
                }
                else echo ' buy - false ';
            } //if
            else { //sell
                echo '[sellAmt '.$sellAmt.'] [target price: >= '.$price.'] [sell price: '.$priceType['sellPrice'].'] ';
                
                if($priceType['sellPrice'] >= $price ) {
                    echo ' sell - true ';
                    //make trade
                    try {
                        $tradeResult = $api->makeOrder($sellAmt, $pair, BTCeAPI::DIRECTION_SELL, $price);  
                    }
                    catch(BTCeAPIInvalidParameterException $e) {
                        echo $e->getMessage();
                    } catch(BTCeAPIException $e) {
                        echo $e->getMessage();
                    }
                    
                    var_dump($tradeResult); 
            
                    $msg = 'Trade successful: Sell '.$buyAmt.' of '.$currency.' at price '.$price;

                    if ($tradeResult['success'] == 1) {
                        sendMail($msg);
                    }; 
                }
                else echo ' sell - false ';
            } //else
            
            echo '<br /><br />';
        } //if
        
    } //foreach
} //while

//echo '<table>'.$apiTrades.'</table>';
?>