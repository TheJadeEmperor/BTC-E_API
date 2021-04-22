<?php 
if ($_POST['submit']) {

    $action = $_POST['action'];
    $ticker = $_POST['ticker'];
    $amt = $_POST['amt'];

    if ($amt) {
        $amtString = ',"amt":"'.$amt.'"';
    }
  
    $json = '{"alert":"DWC","action":"'.$action.'","ticker":"'.$ticker.'"'.$amtString.'}';
}
?>
<form method="POST">
    <input type="text" name="action" value="" placeholder="sell" />
    <input type="text" name="ticker" value="USDT-GT"  placeholder="USDT-GT" />
    <input type="text" name="amt" value="" placeholder="0" />
    <input type="submit" name="submit" value="JSON String" />
    <br /><br />
    <textarea cols="80"><?=$json?></textarea>
</form>

<p>&nbsp;</p>

<textarea cols="80" rows="15">
http://code.bestpayingsites.com/script_bittrex_dwc_trades.php

http://code.bestpayingsites.com/script_gate_dwc_trades.php

http://code.bestpayingsites.com/script_kucoin_dwc_trades.php

http://code.bestpayingsites.com/script_kucoin_dwc_trades.php?sub=kucoin2

http://code.bestpayingsites.com/script_kucoin_dwc_trades.php?sub=kucoin3

http://code.bestpayingsites.com/script_kucoin_dwc_trades.php?sub=kucoin4

</textarea>