<?php
function array_debug($array) {
    echo '<pre>';
    print_r($array);
    echo '</pre>';
}

function message($message) {
    echo "MESSAGE: $message<br />";
}

function daily_rate($rate) {
    return round($rate/365, 4);
}



include_once('include/api_bitfinex.php');


$config = array();
$config['api_key'] = 'jRBpU2fpCAkVUNbCL6BJMuTMlh6MZJax4BKKZUBmNK8';
$config['api_secret'] = 'SzZjbv90Ch57qVXhp3tpf2RjA4CzsnfKaY9SayYc80y';

$bfx = new Bitfinex($config['api_key'], $config['api_secret']);


$balances = $bfx->get_balances();


//array_debug($balances);

//buy
//$res = $bfx->make_trade('0.01', 'btcusd', 'buy', '200');


//sell
//$res = $bfx->make_trade('0.01', 'btcusd', 'sell', '200');
//array_debug($res);

//short
$res = $bfx->make_trade('0.01', 'btcusd', 'short', '1');
array_debug($res);


$r = $bfx->get_positions($data);
array_debug($r);

$cl = $bfx->claim_position($position_id);
array_debug($cl);

exit; 

?>