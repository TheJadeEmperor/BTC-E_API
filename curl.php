<?
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'functions.php');
include($dir.'config.php');

//debug mode or live mode
$server = $_SERVER['SERVER_NAME'];
if ($server == 'localhost' || $server == 'btcAPI.test') {
	$cronjob = 0;
}
else {
    exit;
}


$url = 'https://code.bestpayingsites.com/script_bittrex_dwc_trades.php';
$url = 'http://localhost/btcAPI/script_bittrex_dwc_trades.php';
//$url = 'http://localhost/btcAPI/test_bittrex.php';

$json = array(
    "alert" => "DWC", "action" => "", "ticker" => "USDT-LTC");
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


//log table fields: id | recorded | log
$opt = array(
	'tableName' => $logTableName,
	'cond' => 'ORDER BY recorded desc'
);
$res = dbSelectQuery($opt);

while($log = $res->fetch_array()) {
    $logOutput .= $log['log'].'<br />';
}

echo 'log begin <hr /> '.$logOutput.'';


?>