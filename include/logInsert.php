<?

$output = 'live: '.$live.' | '.$recorded.' | IP: '.$ipAddress.' | post data: '.$data['alert'].' | action: '.$dataAction.' | '.$data['ticker'].' | '.$newline;

$output .= 'bid: '.$bid.' | ask: '.$bid.' | buyQT: '.$buyQT.' sellQT: '.$sellQT.' | orderId: '. $orderId.' '.$newline; 
echo $output;

if($dataAction && $orderId) {
    //write to log db
    $insert = 'INSERT INTO '.$logTableName.' (recorded, log, exchange, action) values ("'.$recorded.'", "'.$output.'",  "gate1",  "'.$dataAction.'")';
    $res = $conn->query($insert);
}

?>