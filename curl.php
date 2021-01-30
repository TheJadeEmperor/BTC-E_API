<?
$url = 'https://code.bestpayingsites.com/script_bittrex_dwc_trades.php';

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
$output = curl_exec($curl);

if($output === FALSE) {
    echo curl_error($curl);
}

curl_close($curl);
?>