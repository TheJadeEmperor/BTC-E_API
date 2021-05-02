<?
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'functions.php');
include($dir.'config.php');

//debug mode only
$server = $_SERVER['SERVER_NAME'];
if ($server == 'localhost' || $server == 'btcAPI.test' || $server == 'btcapi.test') {
	$database = new Database($conn);
    $localHost = 'http://localhost/btcAPI/';
    $serverHost = 'https://code.bestpayingsites.com/';
}
else {
    echo 'Invalid Request';
    exit;
}

//list of scripts 
$exchanges = array(
    'script_binance_dwc_trades', 
    'script_gate_dwc_trades', 
    'script_bittrex_dwc_trades', 
    'script_kucoin1_dwc_trades', 
    'script_kucoin2_dwc_trades', 
    'script_kucoin3_dwc_trades', 
    'script_kucoin4_dwc_trades', 
    'script_kucoin5_dwc_trades', 
);

foreach($exchanges as $ex) {
    
    $dropDown .= '<option value='.$ex.'>'.$ex.'</option>';
}

$exch = $_POST['ex'];

switch($exch) { //URL to call and which exchange to get from log
    case 'script_kucoin5_dwc_trades':  //// KC5 /////
        $url = $serverHost.'script_kucoin_dwc_trades.php?sub=kucoin5';
        $url = $localHost.'script_kucoin_dwc_trades.php?sub=kucoin5';
       
        $ex = 'kucoin5';
        break;
    case 'script_kucoin4_dwc_trades': //// KC4 /////
        $url = $serverHost.'script_kucoin_dwc_trades.php?sub=kucoin4';
        $url = $localHost.'script_kucoin_dwc_trades.php?sub=kucoin4';

        $ex = 'kucoin4';
        break;
    case 'script_kucoin3_dwc_trades': //// KC3 /////
        $url = $serverHost.'script_kucoin_dwc_trades.php?sub=kucoin3';
        $url = $localHost.'script_kucoin_dwc_trades.php?sub=kucoin3';
     
        $ex = 'kucoin3';
        break;
    case 'script_kucoin2_dwc_trades': //// KC2 /////
        $url = $serverHost.'script_kucoin_dwc_trades.php?sub=kucoin2';
        $url = $localHost.'script_kucoin_dwc_trades.php?sub=kucoin2';

        $ex = 'kucoin2';
        break;
    case 'script_kucoin1_dwc_trades':  //// KC1 /////
        $url = $serverHost.'script_kucoin_dwc_trades.php';
        $url = $localHost.'script_kucoin_dwc_trades.php';

        $ex = 'kucoin1';
        break;
    case 'script_binance_dwc_trades': //// Binance ////
        $url = $serverHost.'script_binance_dwc_trades.php';
        $url = $localHost.'script_binance_dwc_trades.php';

        $ex = 'binance';
        break;
    case 'script_bittrex_dwc_trades':  //// Bittrex ////
        $url = $serverHost.'script_bittrex_dwc_trades.php';
        $url = $localHost.'script_bittrex_dwc_trades.php';

        $ex = 'bittrex'; 
        break;
    case 'script_gate_dwc_trades':  //// gate1 /////
        $url = $serverHost.'script_gate_dwc_trades.php';
        $url = $localHost.'script_gate_dwc_trades.php';

        $ex = 'gate1';
        break;
    // default: 
        // exit;
}

if($_POST) {
     $ticker = $_POST['ticker'];
    $action = $_POST['action'];
    $amt = $_POST['amt'];

    echo $ticker.' '.$action.' '.$amt;

    //json data to pass into webhook
    $json = array(
        "alert" => "DWC", //DWC
        "action" => $action, //buy or sell
        "ticker" => $ticker, //USDT-XXX or BTC-KEY
        "amt" => $amt //amt of coin
    ); 


    $data = json_encode($json);

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
}

?>
<p><a href="curl_panic.php">curl_panic</a></p>

<form method="POST">
    <table>
    <tr>
        <td>
            <p>ticker <input type="text" name="ticker" value="<?=$ticker?>" /></p>
            <p>action <input type="text" name="action" value="<?=$action?>" /></p>
            <p>amt <input type="text" name="amt" value="10" /></p>
        </td>
        <td width="10px"></td>
        <td>
            <select name="ex">
                <option></option>
                <?=$dropDown ?>
            </select>
        </td>
    </tr>
    <tr>
    </tr>
    </table>
    <div>
        <input type="submit" />
    </div>
</form>

<?
sleep(2); //delay before showing log

$res = $database->getLogs($ex);

while($log = $res->fetch_array()) {
    $logOutput .= 'id: <b><a href="deleteLog.php?id='.$log['id'].'" target="_BLANK">'.$log['id'].'</a></b> | recorded: '.$log['recorded'].' <br />'.$log['log'].'<br />';
}

echo 'log begin<hr /><br />'.$logOutput.'';

?>