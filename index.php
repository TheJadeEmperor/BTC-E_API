<?php
include('include/config.php');



function priceRow($pair) {
    
    global $allPrices;
    
    return '<td> <a href="https://btc-e.com/exchange/'.$pair.'" target="_blank">'.$allPrices[$pair]['last'].'</a> </td>
            <td> '.$allPrices[$pair]['buy'].' / '.$allPrices[$pair]['sell'].' </td>
            <td> '.$allPrices[$pair]['high'].' / '.$allPrices[$pair]['low'].'</td>';
}


function priceTable($allPrices) {
    echo '
        <table border="1" class="table">
            <tr>
                <td>Pair</td>
                <td>Last Price</td>
                <td>Buy/Sell</td>
                <td>High/Low</td>
            </tr>
            <tr>
                <td>BTC/USD</td>
                '.priceRow('btc_usd').'
            </tr>
            <tr>
                <td> LTC/USD </td>
                '.priceRow('ltc_usd').'
            </tr>
            <tr>
                <td> LTC/BTC </td>
                '.priceRow('ltc_btc').'            
            </tr>
            <tr>
                <td> EUR/USD </td>
                '.priceRow('eur_usd').'            
            </tr>
        </table>';
}


function retrieveJSON($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    $result = curl_exec($ch);
    curl_close($ch);

    $json = json_decode($result, true);
    return $json;
}

//get price for BTC-E currency pair
function getPriceData($pair) {
    global $public_api;
    $json = retrieveJSON($public_api.'ticker/'.$pair);        
    return $json[$pair];
}

global $public_api;
$public_api = 'https://btc-e.com/api/3/';

//BTC-E prices 
$currencyPair = array('btc_usd', 'ltc_usd', 'ltc_btc', 'eur_usd');
 
foreach($currencyPair as $cPair) {
    $allPrices[$cPair] = getPriceData($cPair);
}


if($_POST['submit_options']) {
    $btc_e_currency = $_POST['btc_e_currency'];
    $btc_e_balance = $_POST['btc_e_balance'];
    $btc_e_trading = $_POST['btc_e_trading'];
    $bitfinex_currency = $_POST['bitfinex_currency'];
    $bitfinex_balance = $_POST['bitfinex_balance'];
    $bitfinex_trading = $_POST['bitfinex_trading'];
    
    //do multiple queries in one call
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, 0);
    
    $queryO = 'UPDATE '.$context['optionsTable'].' SET
            setting = "'.$btc_e_currency.'" WHERE opt = "btc_e_currency";               
        UPDATE '.$context['optionsTable'].' set
            setting = "'.$btc_e_trading.'" WHERE opt = "btc_e_trading";
        UPDATE '.$context['optionsTable'].' set
            setting = "'.$btc_e_balance.'" WHERE opt = "btc_e_balance";
        UPDATE '.$context['optionsTable'].' set
            setting = "'.$bitfinex_currency.'" WHERE opt = "bitfinex_currency";
        UPDATE '.$context['optionsTable'].' set
            setting = "'.$bitfinex_balance.'" WHERE opt = "bitfinex_balance";
        UPDATE '.$context['optionsTable'].' set
            setting = "'.$bitfinex_trading.'" WHERE opt = "bitfinex_trading";
    ';
   
    try {
        $db->exec($queryO);
    }
    catch (PDOException $e) {
        echo $e->getMessage();
        die();
    }
}

$queryO = $db->query('SELECT * FROM '.$context['optionsTable'].' ORDER BY opt');

foreach($queryO as $opt){ 
    //echo $opt['opt'].' '.$opt['setting'].'<br>';

    $btc_e_option[$opt['opt']][$opt['setting']] = 'selected';
    $bitfinex_option[$opt['opt']][$opt['setting']] = 'selected';
    
    if($opt['opt'] == 'btc_e_balance') {
        $btc_e_balance = $opt['setting'];
    }
    else if($opt['opt'] == 'bitfinex_balance') {
        $bitfinex_balance = $opt['setting'];
    }
}

//print_r($btc_e_trading_option);//

$btc_e_trading_dropdown = '<select name="btc_e_trading">
    <option '.$btc_e_option['btc_e_trading'][1].'>1</option>
    <option '.$btc_e_option['btc_e_trading'][0].'>0</option>
</select>
';

$btc_e_currency_dropdown = '<select name="btc_e_currency">
    <option '.$btc_e_option['btc_e_currency']['btc'].'>btc</option>
    <option '.$btc_e_option['btc_e_currency']['ltc'].'>ltc</option>
</select>';


$bitfinex_trading_dropdown = '<select name="bitfinex_trading">
    <option '.$bitfinex_option['bitfinex_trading'][1].'>1</option>
    <option '.$bitfinex_option['bitfinex_trading'][0].'>0</option>
</select>';

$bitfinex_currency_dropdown = '<select name="bitfinex_currency">
    <option '.$bitfinex_option['bitfinex_currency']['btc'].'>btc</option>
    <option '.$bitfinex_option['bitfinex_currency']['ltc'].'>ltc</option>
</select>';

$queryP = $db->query('SELECT * FROM api_prices order by count desc'); 

foreach($queryP as $p) { 
    $priceHistory .= '<tr>
        <td>'.$p['time'].'</td>
        <td>'.$p['btce_btc'].'</td>
        <td>'.$p['btce_ltc'].'</td>
        <td>'.$p['bitfinex_btc'].'</td>
        <td>'.$p['bitfinex_ltc'].'</td>';
}

$priceHistory = '<table class="table">
    <tr><td>time</td>
    <td>btce_btc</td>
    <td>btce_ltc</td>
    <td>bitfinex_btc</td>
    <td>bitfinex_ltc</td>
    </tr>
'.$priceHistory.'</table>';

/*
$queryT = $db->query('SELECT * FROM '.$context['tradesTable'].' order by pair, action, price');

foreach($queryT as $t) { 
    $id = $t['id'];
    $usd = $t['price'] * $t['amount'];
    
    $tradeRow = '<tr><td>'.$t['action'].'</td><td>'.$t['pair'].'</td><td> '.$t['price'].'</td>
        <td>'.$t['amount'].'</td><td>'.$usd.'</td>
        <td><input type="button" class="btn btn-success updateButton" value=" Update " onclick="fillForm(\''.$id.'\')" /> </td>
        <td><input type="button" class="btn btn-danger deleteButton" value="X" onclick="deleteRow(\''.$id.'\')" /></td>
            </tr>';
    
    if($t['status'] == 'A')
        $activeTrades .= $tradeRow;
    else
        $disabledTrades .= $tradeRow;
}
*/
?>
<head>
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">

<!-- Latest compiled and minified JavaScript -->
<!--<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>-->

<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css" />

<script src="http://code.jquery.com/jquery-latest.min.js" type='text/javascript' /></script>
<script src="include/jquery-ui/ui/jquery-ui.js"></script>
<style>
    body {
        margin: 5px 15px;
    }
</style>
<script>
    function updateRow(id) {
        $.ajax({ //update this record
            type        : 'POST', //Method type
            url         : '<?=$update_call?>', 
            data        : $('#updateForm').serialize(), 
            success     : function(msg) {
                 //alert(msg)
                 location.reload();
             }
        });
        event.preventDefault(); //Prevent the default submit      
    }
    
    function fillForm(id) {
        //alert(id);
        $.getJSON("<?=$read_call?>&id="+id+"", function( data ) {
            
            $.each( data, function( key, val ) {
                if(data.hasOwnProperty(key))
                    $('input[name='+key+']').val(val);

                if(key == 'id') $('input[name=idButton]').val(val);

                if(key == 'action') {
                    $('select[name="action"]').find('option[value="'+val+'"]').attr("selected",true);
                }
                else if(key == 'pair') {
                    $('select[name="pair"]').find('option[value="'+val+'"]').attr("selected",true);
                }
            });
        });
    }
    
    function deleteRow(id) {
        if (confirm("Are you sure you want to delete record "+id+"?")) {
            $.ajax({
                type: "GET",
                url: "<?=$delete_call?>&id="+id,
                success: function() {
                    alert('Deleted record '+id);
                    location.reload();
                }
            })
        }    
    }
    
    function createRow() {
        $.ajax({
            type: "POST",
            url: "<?=$create_call?>",
            data: $('#updateForm').serialize(),
            success: function(msg) {
                alert('server msg: '+msg);
                location.reload();
            }
        });
    }
    
    function copyValue() {
        var file = $("#file").val();
        var subject = file.substring(5 ,file.length - 5);
        $("#subject").val(subject);
    }
 
    $(document).ready(function () {
        $('#updateForm').hide();
        $('#priceTable').hide();
        
        
        $(".updateButton").click(function () {
            $("#updateForm").dialog({
                modal: true,
                width: 750,
                position: 'top',
                show: {
                    effect: "explode",
                    duration: 500
                },
                hide: {
                    effect: "explode",
                    duration: 500
                },
                buttons: {
                    Save: function () {
                        var id = $('#id').val();
                        updateRow(id);
                        $( this ).dialog( "close" );
                    },
                    Cancel: function() {
                        $( this ).dialog( "close" );
                    },                        
                }
            });
        });
        
         $(".priceTable").click(function() {
            $("#priceTable").dialog({
                modal: true,
                width: 700,
                position: 'top',
                show: {
                    effect: "explode",
                    duration: 500
                },
                hide: {
                    effect: "explode",
                    duration: 500
                },
                buttons: {
                    Close: function() {
                        $(this).dialog("close");
                    }
                }
            })
        })
        
        $(".createButton").click(function() {
            $("#updateForm").dialog({
                modal: true,
                width: 700,
                position: 'top',
                show: {
                    effect: "explode",
                    duration: 500
                },
                hide: {
                    effect: "explode",
                    duration: 500
                },
                buttons: {
                    Save: function() {
                        createRow();
                        $(this).dialog("close");
                    },
                    Cancel: function() {
                        $(this).dialog("close");
                    }
                }
            })
        });

    });
    </script>
</head>    
<body>
    <br />

    <table>
    <tr valign="top">
        <td width="">
                    
            <h3>Trade Options</h3> 
            
            <form method="POST">
            <table>
                <tr valign="top">
                    <td>
                        <pre class="xdebug-var-dump">
                        <table class="table" border="1">
                        <tr>
                            <td>BTC-E Trading</td>
                            <td>
                                <?=$btc_e_trading_dropdown?>
                            </td>
                        </tr>
                        <tr>
                            <td>BTC-E Currency</td>
                            <td>
                                <?=$btc_e_currency_dropdown?>
                            </td>
                        </tr>
                        <tr>
                            <td>% of Balance</td>
                            <td>
                                <input type="text" name="btc_e_balance" value="<?=$btc_e_balance?>" />
                            </td>
                        </tr>
                        </table>
                        </pre>
                    </td>
                        <td>
                        <pre class="xdebug-var-dump">
                        <table class="table" border="1">
                            <tr>
                                <td>Bitfinex Trading</td>
                                <td>
                                    <?=$bitfinex_trading_dropdown?>
                                </td>
                            </tr>
                            <tr>
                                <td>Bitfinex Currency</td> 
                                <td>
                                    <?=$bitfinex_currency_dropdown?>
                                </td>
                            </tr>
                            <tr>
                                <td>% of Balance</td> 
                                <td>
                                    <input type="text" name="bitfinex_balance" value="<?=$bitfinex_balance?>" />
                                </td>
                            </tr>
                        </table>
                        </pre>
                        </td>
                </tr>
                <tr>
                    <td colspan="2" align="center"> <input type="submit" class="btn btn-success" name="submit_options" value="Update" /></td>
                </tr>
            </table>
            </form>
            
            <br />

            <h3>View Prices History</h3>
            <input type="button" class="btn btn-warning updateButton" value="Prices History" />
 
            
            <h3>Price Chart</h3>
            <input type="button" class="btn btn-warning priceTable" value="Prices History" />
            
            <h3>apiTrade Debug Mode</h3>
            <a href="apiTrade.php?debug=1" target="_blank"><input type="button" class="btn btn-warning" value="apiTrade.php" /></a> &nbsp;
            
            <a href="apiTradeBitfinex.php?debug=1" target="_blank"><input type="button" class="btn btn-warning" value="apiTradeBitfinex.php" /></a>
            
        </td>
        <td width="60px"></td>
        
        <td>
            
            <div id="btc_e_links">
            <h3>BTC-E Links</h3>
            <pre class="xdebug-var-dump">
            <table class="">
                <tr valign="top">
                    <td>
                        <a href="https://btc-e.com/" target="_blank">Main Site</a>
                        <br /><br />
                        <a href="https://btc-e.com/profile#orders_history" target="_blank">Orders History</a>
                        <br /><br />
                        <a href="https://btc-e.com/profile#trade_history" target="_blank">Trade history</a> 
                        <br /><br />                        
                    </td>
                    <td>
                        <a href="https://btc-e.com/profile#funds" target="_blank">Account Funds</a> 
                        <br /><br />
                        <a href="https://btc-e.com/profile#funds/deposit_coin/1" target="_blank">Deposit Coins</a>
                        <br /><br />
                        <a href="https://btc-e.com/profile#funds/withdraw_coin/1" target="_blank">Withdraw Coins</a>
                        <br /><br />
                    </td>
                </tr>
            </table>
            </pre>
            </div>
            
            <div id="bitcoin_wisdom_links">
            <h3>Bitcoin Wisdom Links</h3>
            <pre class="xdebug-var-dump">
            <table class="">
                <tr valign="top">
                    <td>
                        
                        <a href="https://bitcoinwisdom.com/markets/btce/btcusd" target="_blank">/markets/btce/btcusd</a>
                        <br /><br />
                        
                        <a href="https://bitcoinwisdom.com/markets/btce/ltcusd" target="_blank">/markets/btce/ltcusd</a>
                        
                        <br /><br />
                        
                        <a href="https://bitcoinwisdom.com/markets/bitfinex/btcusd" target="_blank">/markets/bitfinex/btcusd</a>
                         
                        <br /><br />
                        
                        <a href="https://bitcoinwisdom.com/markets/bitfinex/ltcusd" target="_blank">/markets/bitfinex/ltcusd</a>
                    </td>
                </tr>
            </table>
            </pre>
        </td>
    </tr>
</table>

<form id="priceTable" title="Price Table">
    <?=priceTable($allPrices)?>
</form>

<form id="updateForm" title="Price History">

    <?=$priceHistory?>
    
<!--    ID <input type="button" name="idButton" />
    <br />

    <table>
        <tr>
            <td>Pair</td>
            <td> <select name="pair" id="pair">
                    <option value="btc_usd">BTC/USD</option>
                    <option value="ltc_usd">LTC/USD</option>
                    <option value="ltc_btc">LTC/BTC</option>
                    <option value="eur_usd">EUR/USD</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>Action</td>
            <td>
                <select name="action" id="action">
                    <option value="buy">buy</option>
                    <option value="sell">sell</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>Price</td>
            <td>
                <input type="text" name="price" />
            </td>
        </tr>
        <tr>
            <td>Amount</td>
            <td>
                <input type="text" name="amount" />
            </td>
        </tr>
        
    </table>
  
    <input type="hidden" name="id" />
        
    Status 
    <select name="status">
        <option value="A">Active</option>
        <option value="D">Disabled</option>
    </select>-->
        
</form>