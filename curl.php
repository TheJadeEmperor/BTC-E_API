<?
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'functions.php');
include($dir.'config.php');

//debug mode only
$server = $_SERVER['SERVER_NAME'];
if ($server == 'localhost' || $server == 'btcAPI.test') {
	$cronjob = 0;
    $localHost = 'http://localhost/btcAPI/';
    $serverHost = 'https://code.bestpayingsites.com/';
}
else {
    echo 'Invalid Request';
    exit;
}

//list of scripts 
$exchanges = array(
    'script_bittrex_dwc_trades', 
    'script_binance_dwc_trades', 
    'script_kucoin_dwc_trades', 
);

foreach($exchanges as $ex) {
    echo '<p><a href="curl.php?ex='.$ex.'">'.$ex.'</a></p>'; 
}


if($_GET['ex'] == 'script_bittrex_dwc_trades') {
    //urls to use for curl
    $url = $serverHost.'script_bittrex_dwc_trades.php';
    $url = $localHost.'script_bittrex_dwc_trades.php';
    $url = $localHost.'test_bittrex.php';
    $cond = ' exchange="bittrex"';
}
else if ($_GET['ex'] == 'script_binance_dwc_trades') {
    $url = $serverHost.'script_binance_dwc_trades.php';
    $url = $localHost.'script_binance_dwc_trades.php';
    $cond = ' exchange="binance"';
}
else if ($_GET['ex'] == 'script_kucoin_dwc_trades') {
    $url = $serverHost.'script_kucoin_dwc_trades.php';
    $url = $localHost.'script_kucoin_dwc_trades.php';
    $cond = ' exchange="kucoin1"';
}
else {
    exit;
}


$json = array(
    "alert" => "DWC", "action" => "buy", "ticker" => "USDT-DOGE");
$data = json_encode($json);

//print_r($data_string);

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

curl_setopt($curl, CURLOPT_HTTPHEADER, array ( 
    'Content-Type: application/json', 
    'Content-Length: ' . strlen($data)) 
);

$output = curl_exec($curl);

if($output === FALSE)
    echo curl_error($curl);
else 
    echo 'curl begin <hr /> '.$output.' <hr /> curl end';

curl_close($curl);

echo '<br /><br />';

sleep(2); //delay before showing log


//log table fields: id | recorded | log | exchange | action
$opt = array(
	'tableName' => $logTableName,
	'cond' => ' WHERE'. $cond.' ORDER BY recorded desc'
);
$res = dbSelectQuery($opt);

while($log = $res->fetch_array()) {
    $logOutput .= $log['log'].'<br />';
}

echo 'log begin <hr /> '.$logOutput.'';


?>