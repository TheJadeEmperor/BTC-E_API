<?

$output = 'live: '.$live.' | '.$recorded.' | IP: '.$ipAddress.' | action: '.$dataAction.' | '.$data['ticker'].' | '.$newline;

$output .= 'bid: '.$bid.' | ask: '.$bid.' | buyQT: '.$buyQT.' sellQT: '.$sellQT.' | orderId: '. $orderId.' '.$newline; 

if($totalBalance) {
    $output .= 'coinBalance: '.$coinBalance.' | USDTBalance: '. $USDTBalance.' | totalBalance: '.$totalBalance.$newline; 
}

echo $output; //show output before inserting into log

//only log valid actions and valid orders 
if($dataAction && $orderId) { 
    //write to log db
    $insert = 'INSERT INTO '.$logTableName.' (recorded, log, exchange, action) values ("'.$recorded.'", "'.$output.'",  "'.$sub.'",  "'.$dataAction.'")';
    $res = $conn->query($insert) or print($conn->error);
}

?>