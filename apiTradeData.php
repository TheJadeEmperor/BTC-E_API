<?php
include('include/config.php'); 

$debug = $_GET['debug'];

if($debug == 1) {
    echo '<< debug mode >>'; $newline = '<br>';
}
else {
    $newline = "\n";
}

$output .= $newline.'exchange [currency] [last_action] [last_price] [trade_signal] [last_updated ]'.$newline;

$queryD = $db->query('SELECT * FROM '.$context['tradeDataTable'].' ORDER BY exchange');

foreach($queryD as $row){ 
    $output .= $newline;
    $output .= $row['exchange'].' ['.$row['currency'].'] ['.$row['last_action'].'] ['.$row['last_price'].'] ['.$row['trade_signal'].'] [ '.$row['last_updated'].' ]';
    $output .= $newline;  
}

//if($debug != 1) {
    //back up api_prices table with today's date as the name
    $md = date('m_d', time());
    $newTable = $context['pricesTable'].'_'.$md;

    $queryCreate = 'CREATE TABLE '.$newTable.' LIKE '.$context['pricesTable'].';';
    $db->exec($queryCreate);
    
    $queryCount = 'SELECT COUNT(*) as count FROM '.$newTable.';';
    $resCount = $db->query($queryCount);
    foreach($resCount as $countRows){}
        
    $queryInsert = 'INSERT '.$newTable.' SELECT * FROM '.$context['pricesTable'].';';
    
    if($countRows['count'] == 0) { //make sure there are no rows in the new table
        $db->query($queryInsert);
    }
    
    //delete table from 4 days ago
    $x_days_ago = date('m_d', mktime(0, 0, 0, date("m") , date("d") - 4, date("Y")));
    $oldTable = $context['pricesTable'].'_'.$x_days_ago;
    
    $queryDrop = 'DROP TABLE IF EXISTS '.$oldTable.'';
    $db->query($queryDrop);
    
   
    
    $output .= $newline.$newline.$queryCreate.$newline.$newline.$queryInsert.$newline.$newline.$queryDrop.$newline.$newline;
//}
    

echo $output;

print_r($db->errorInfo());
?>