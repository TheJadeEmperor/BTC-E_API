<?php
$latestPrice = $_POST['latestPrice'];
$ema10 = $_POST['ema10'];
$ema21 = $_POST['ema21'];

$debugURL = 'apiTradeBitfinex.php?debug=1&latestPrice='.$latestPrice.'&ema10='.$ema10.'&ema21='.$ema21;
?>
<form method="post">
    latestPrice: <input type="text" name="latestPrice" value="<?=$latestPrice?>" /><br />
    ema10: <input type="text" name="ema10" value="<?=$ema10?>" /><br />
    ema21: <input type="text" name="ema21" value="<?=$ema21?>" /><br />
    <input type="submit" value="Submit" />
</form>
<a href="<?=$debugURL?>" target="_blank">Click here</a>