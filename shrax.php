<?php
include('include/config.php');

$price_data = array();
$res = $db->query('SELECT * FROM shrax_prices WHERE day >= "2015-01-15" order by day asc');

foreach($res as $daily) {
    array_push($price_data, $daily);
    
}

$balance = '2000'; $shares = 0;

foreach($price_data as $count => $daily) {

    $delta = $daily['delta'];
    $day = $daily['day'];
    $ema_30 = $daily['ema_30'];
    $closing_price = $daily['closing_price'];

    $delta_1 = $price_data[$count-1]['delta'];
    $delta_2 = $price_data[$count-2]['delta'];
    
    $table .= '<tr><td>'.$day.'</td><td>'.$closing_price.'</td><td>'.$delta.'</td>';

    $trend = ''; 
    if($closing_price < $ema_30) { //under

        if($last_action == 'buy' && $up_or_down == 'over') {
            $balance = $closing_price * $shares;
            $last_action = 'sell';

            $trend .= ' - sell '.number_format($shares, 3).' shares';
            $shares = 0;
        }

        if($last_action != 'buy') //check if you already bought shares
        if($delta > 0 && $delta_1 > 0 && $delta_2 > 0) {
            $shares = $balance/$closing_price; 
            $last_action = 'buy';
            $trend = 'uptrend - buy '.number_format($shares, 3).' shares';
        }
        
        $over_or_under = 'under';
    }
    else if ($closing_price > $ema_30) { //over 
    
        
        if($delta < 0) {
            $trend = 'downtrend';
            if($last_action == 'buy') {
                $balance = $closing_price * $shares;
                $last_action = 'sell';
                
                $trend .= ' - sell '.number_format($shares, 3).' shares';
                $shares = 0;
            }
        }
        
        $over_or_under = 'over';
    } 
    
    $table .= '<td>'.$over_or_under.'<td>'.$trend.'</td>';
    
    if($last_action == 'buy')
        $balance = $closing_price * $shares;
    $table .= '<td> $'.number_format($balance, 2).'</td></tr>';
    
//    echo '<br>';
}

echo '<table border=1 cellspacing=0>'.$table.'</table>';

exit;

$res = $db->query('SELECT * FROM shrax_prices order by day desc');

foreach($res as $row) {
    echo $row['day'].'';
    
    if($row['day'] >= '2015-01-15') {
        $ma = 'SELECT AVG(closing_price) as ma FROM shrax_prices WHERE day < "'.$row['day'].'" AND day >= DATE_SUB("'.$row['day'].'", INTERVAL 30 DAY)';
        $avg = $db->query($ma);
        
        foreach($avg as $a) { echo ' '.$a['ma']; }
        
        $upd = $db->query('UPDATE shrax_prices SET ema_30="'.$a['ma'].'" WHERE day="'.$row['day'].'"');
    }
    
    echo '<br>';
}

exit; 
$apiURL = 'https://www.quandl.com/api/v3/datasets/YAHOO/FUND_SHRAX.json?auth_token=p5pDNNLQYyyzGWTuizat&start_date=2014-12-15';
$contentsURL = file_get_contents($apiURL);
   
$priceData = json_decode($contentsURL);
    
//echo '<pre>'.print_r($priceData->dataset->data).'</pre>';

foreach($priceData->dataset->data as $p) {
//    echo $p[0].' ... '.$p[4].' ... <br>';

    //store in database
/*    $db->query('INSERT INTO shrax_prices (day, closing_price) values (
        "'.$p[0].'", "'.$p[4].'")');*/
}


$url = 'https://www.quandl.com/api/v3/datasets/YAHOO/FUND_SHRAX.json?auth_token=p5pDNNLQYyyzGWTuizat&transform=diff&start_date=2014-12-15';

$contentsURL = file_get_contents($url);
   
$changeData = json_decode($contentsURL);

foreach($changeData->dataset->data as $c) {
//    echo $c[0].' ... '.$c[4].' ... <br>';
echo '<br>';
echo    $q = 'UPDATE shrax_prices SET delta='.$c[4].' WHERE day="'.$c[0].'"';
    //store in database
    $db->query($q);
}


function EMACalculator($limit,$array)
{
    $EMA_previous_day = $array[0];
    //print_r($array);
    $multiplier1 = (2/$limit+1);
    $EMA[]=array();
    $EMA = $array[0];
    $Close= $array[1];
    
    while($limit)
{    
    //echo"EMA is $EMA\n";
    $EMA = ($Close - $EMA_previous_day) * $multiplier1 + $EMA_previous_day;
    $EMA_previous_day= $EMA;
    
    $limit--;
}
return $EMA;
}
//where $limit accept the period of ema  and $array : accept array of data for ema calculation.

?>