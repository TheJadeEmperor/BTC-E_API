<?php
include_once('include/api_bitfinex.php');
include('include/config.php');

define('API_KEY','lhB8h0aZSH8v49CbIM6Ib3bee6tHQ8aZZbmbTjf2OYM');
define('API_SECRET','VHcTwmMwEoy2jBhGvMcldBZ5dy9OjosKuynmut05dcw');
$call = new Bitfinex(API_KEY, API_SECRET);


function array_debug($array) {
    echo '<pre>';
    print_r($array);
    echo '</pre>';
}


$acc = $call->margin_infos();

array_debug($acc);

$marginUSD = $acc[0]['margin_limits'][0]['tradable_balance'];

$output .= 'tradable btc: '.number_format($tradable['btc'], 4).' - tradable ltc: '.number_format($tradable['ltc'], 4).'';

echo $output; 
?>