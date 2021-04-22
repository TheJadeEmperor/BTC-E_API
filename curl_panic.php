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
    'panic_bittrex',
    'panic_gate',
    'panic_binance',
    'panic_kucoin_1', 
    'panic_kucoin_2', 
    'panic_kucoin_3', 
    'panic_kucoin_4', 	
    'panic_kucoin_5', 
);


foreach($exchanges as $ex) {
    //echo '<p><a href="curl_panic.php?ex='.$ex.'">'.$ex.'</a></p>'; 

    $dropDown .= '<option value='.$ex.'>'.$ex.'</option>';
}

$exch = $_POST['ex'];

switch($exch) { //URL to call and which exchange to get from log
    case 'panic_bittrex':  //// Bittrex /////
        $url = $serverHost.'panic_bittrex.php';
        $url = $localHost.'panic_bittrex.php';
       
        $ex = 'bittrex';
        break;
    case 'panic_gate':  //// Gate /////
        $url = $serverHost.'panic_gate.php';
        $url = $localHost.'panic_gate.php';
        
        $ex = 'gate';
        break;
    case 'panic_binance':  //// Binance /////
        $url = $serverHost.'panic_binance.php';
        $url = $localHost.'panic_binance.php';
       
        $ex = 'binance';
        break;
    case 'panic_kucoin_1':  //// Kucoin1 /////
        $url = $serverHost.'panic_kucoin.php?sub=kucoin1';
        $url = $localHost.'panic_kucoin.php?sub=kucoin1';
        
        $ex = 'kucoin1';
        break;
    case 'panic_kucoin_2':  //// Kucoin2 /////
        $url = $serverHost.'panic_kucoin.php?sub=kucoin2';
        $url = $localHost.'panic_kucoin.php?sub=kucoin2';
        
        $ex = 'kucoin2';
        break;
    case 'panic_kucoin_3':  //// Kucoin3 /////
        $url = $serverHost.'panic_kucoin.php?sub=kucoin3';
        $url = $localHost.'panic_kucoin.php?sub=kucoin3';
        
        $ex = 'kucoin3';
        break;
    case 'panic_kucoin_4':  //// Kucoin4 /////
        $url = $serverHost.'panic_kucoin.php?sub=kucoin4';
        $url = $localHost.'panic_kucoin.php?sub=kucoin4';
        
        $ex = 'kucoin4';
        break;
    case 'panic_kucoin_5':  //// Kucoin5 /////
        $url = $serverHost.'panic_kucoin.php?sub=kucoin5';
        $url = $localHost.'panic_kucoin.php?sub=kucoin5';
        
        $ex = 'kucoin5';
        break;
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
<form method="POST">
    <table>
    <tr>
        <td>
            <p>ticker <input type="text" name="ticker" value="<?=$ticker?>" /></p>
            <p>action <input type="text" name="action" value="<?=$action?>" /></p>
            <p>amt <input type="text" name="amt" value="" /></p>
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