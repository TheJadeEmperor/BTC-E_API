<?php
include('include/config.php'); 
include('include/api_btc_e.php');
include('include/api_bitfinex.php');

$debug = $_GET['debug'];

if($debug == 1) {
    echo '<< debug mode >>'; $newline = '<br>';
}
else {
    $newline = "\n";
}

$output .= $newline.'exchange | currency | last_action | last_price | trade_signal | last_updated'.$newline;

$queryD = $db->query('SELECT * FROM '.$context['tradeDataTable'].' ORDER BY last_updated LIMIT 20');

foreach($queryD as $row){ 
    $output .= $newline;
    $output .= $row['exchange'].' | '.$row['currency'].' | '.$row['last_action'].' | '.$row['last_price'].' | '.$row['trade_signal'].' | '.$row['last_updated'].'';
    $output .= $newline;  
}


    //back up api_prices table with today's date as the name
    $md = date('m_d', time());
    $newTable = $context['pricesTable2h'].'_'.$md;

    $queryCreate = 'CREATE TABLE '.$newTable.' LIKE '.$context['pricesTable2h'].';';
    $db->exec($queryCreate);
    
    $queryCount = 'SELECT COUNT(*) as count FROM '.$newTable.';';
    $resCount = $db->query($queryCount);
    foreach($resCount as $countRows){}
        
    $queryInsert = 'INSERT '.$newTable.' SELECT * FROM '.$context['pricesTable2h'].';';
    
    if($countRows['count'] == 0) { //make sure there are no rows in the new table
        $db->query($queryInsert);
    }
    
    //delete table from 4 days ago
    $x_days_ago = date('m_d', mktime(0, 0, 0, date("m") , date("d") - 4, date("Y")));
    $oldTable = $context['pricesTable2h'].'_'.$x_days_ago;
    
    $queryDrop = 'DROP TABLE IF EXISTS '.$oldTable.';';
    $db->query($queryDrop);
    

    $output .= $newline.$newline.$queryCreate.$newline.$newline.$queryInsert.$newline.$newline.$queryDrop.$newline.$newline;
    
    //get daily balances from Bitfinex

    //connect to Bitfinex
    $bitfinexCall = new Bitfinex(BITFINEX_API_KEY, BITFINEX_API_SECRET); 
    $acctFunds = $bitfinexCall->get_balances(); //call get_balances function
    $acctUSD = $acctFunds[8]['amount']; //USD balance
    
    $balanceBitfinex = number_format($acctUSD, 2); //account balance
    
    $todaysDate = date('Y-m-d', time());
    
    //check for existing entries for today's date
    $queryCount = 'SELECT COUNT(*) as count FROM '.$context['balanceTable'].' WHERE date="'.$todaysDate.'";';
    $resCount = $db->query($queryCount);
    foreach($resCount as $countRows){}
    
   
    //update api_balance table    
    $queryBalance = 'INSERT INTO '.$context['balanceTable'].' (date, balance_bitfinex) VALUES ("'.$todaysDate.'", "'.$balanceBitfinex.'");';

    if($countRows['count'] == 0) { //make sure there are no rows for today's date
        $db->query($queryBalance); //insert balance data into db
    }
    else { //more than 1 row for today - delete all rows except 1 row
        $queryDelete = 'DELETE FROM '.$context['balanceTable'].' WHERE date="'.$todaysDate.'"
            LIMIT '.($countRows['count'] - 1).';';
        $db->query($queryDelete);
    }
    
    //echo $countRows['count'];
    
    $output .= 'Bitfinex balance: '.$balanceBitfinex.$newline.$newline;
    
    
echo $output;

print_r($db->errorInfo());
?>