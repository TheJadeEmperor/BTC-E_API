<?php
include('apiPrices.php');


if($_POST['tradeNew']) {

    $tradePair = $_POST['tradePair'];
    $tradeAction = $_POST['tradeAction'];
    $tradePrice = $_POST['tradePrice'];
    $tradeAmount = $_POST['tradeAmount'];
    
    //insert into db
    $insertTrade = "INSERT INTO ".$context['tradesTable']." (
        status, action, pair, price, amount
        ) VALUES (
        'D', '$tradeAction', '$tradePair', '$tradePrice', '$tradeAmount'
        )";
    mysql_query($insertTrade, $conn) or die(mysql_error());
   
}


$sel = 'SELECT * FROM '.$context['tradesTable'].' order by pair, action, price';
$res = mysql_query($sel, $conn) or die(mysql_error());

while($t = mysql_fetch_assoc($res)) {
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

        
$read_call = 'include/ajax.php?action=read';
$update_call = 'include/ajax.php?action=update';
$delete_call = 'include/ajax.php?action=delete';
$create_call = 'include/ajax.php?action=create';
?>
<head>
<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css" />

<link href="include/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css" media="screen" />
<link href="include/bootstrap/css/bootstrap-theme.css" rel="stylesheet" type="text/css" media="screen" />

<script src="http://code.jquery.com/jquery-latest.min.js" type='text/javascript' /></script>
<script src="include/jquery-ui/ui/jquery-ui.js"></script>
<script src="include/bootstrap/js/bootstrap.js"></script>
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
        
        $(".updateButton").click(function () {
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
        
        
        (function($) {
    $.fn.countTo = function(options) {
        // merge the default plugin settings with the custom options
        options = $.extend({}, $.fn.countTo.defaults, options || {});

        // how many times to update the value, and how much to increment the value on each update
        var loops = Math.ceil(options.speed / options.refreshInterval),
            increment = (options.to - options.from) / loops;

        return $(this).each(function() {
            var _this = this,
                loopCount = 0,
                value = options.from,
                interval = setInterval(updateTimer, options.refreshInterval);

            function updateTimer() {
                value += increment;
                loopCount++;
                
                var timer = Math.floor(value.toFixed(options.decimals)/1000);
                $(_this).html(timer);

                if (typeof(options.onUpdate) == 'function') {
                    options.onUpdate.call(_this, value);
                }

                if (loopCount >= loops) {
                    clearInterval(interval);
                    value = options.to;

                    if (typeof(options.onComplete) == 'function') {
                        options.onComplete.call(_this, value);
                    }
                }
            }
        });
    };

    $.fn.countTo.defaults = {
        from: 0,  // the number the element should start at
        to: 100,  // the number the element should end at
        speed: 1000,  // how long it should take to count between the target numbers
        refreshInterval: 100,  // how often the element should be updated
        decimals: 0,  // the number of decimal places to show
        onUpdate: null,  // callback method for every time the element is updated,
        onComplete: null,  // callback method for when the element finishes updating
    };
    })(jQuery);

        function refreshPriceTable(){
            $('#priceTable').html('Wait...');
            $('#priceTable').load('priceTable.php');

            jQuery(function($) {
                    $('.timer').countTo({
                        from: 31000,
                        to: 100,
                        speed: 31000,
                        refreshInterval: 1000,
                        onComplete: function(value) {
                            console.debug(this);
                        }
                    });
                });
        }

        refreshPriceTable();
        
        setInterval(function() {
            
            refreshPriceTable();

        }, 30000); //30 seconds 
        
    });
    </script>
</head>    
<body>
        
    <br />

    <table>
    <tr valign="top">
        <td width="500px">
                    
            <h3>Price Table <span class="timer"></span></h3>

            <div id="priceTable"></div>

            <br />
    
            <h3>Links</h3>
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
            
            <h2>Account Data</h2>
            
            <div id="accountData">
            <?
            $acctFunds = $acctInfo['return']['funds'];

            var_dump($acctInfo);
            ?>
            </div>
              
       <form method="POST">
            <table>
                <tr>
                    <td>Pair</td>
                    <td> <select name="tradePair">
                            <option value="btc_usd">BTC/USD</option>
                            <option value="ltc_usd">LTC/USD</option>
                            <option value="ltc_btc">LTC/BTC</option>
                            <option value="ltc_btc">NVC/USD</option>
                            <option value="ltc_btc">NMC/USD</option>
                            
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        Action
                    </td>
                    <td>
                        <select name="tradeAction">
                            <option value="buy">Buy</option>
                            <option value="sell">Sell</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        Price
                    </td>
                    <td>
                        <input type="text" name="tradePrice" />
                    </td>
                </tr>
                <tr>
                    <td>
                        Amount
                    </td>
                    <td>
                        <input type="text" name="tradeAmount" />
                    </td>
                </tr>
            </table>

            <center>
                <input type="submit" name="tradeNew" onclick="confirm('Are you sure?')" />
            </center>
            </form>
        
        
        </td>
        <td width="60px"></td>
        </td>
        <td>
            
            <h3>Pending Trades</h3>
            <table border="1">
                <tr>
                    <th width="80px">Action</th>
                    <th width="80px">Pair</th>
                    <th width="80px">Price</th>
                    <th width="80px">Amount</th>
                    <th>Approx USD</th>
                    <th colspan="2">Edit</th>
                </tr>
                <?=$activeTrades?>
            </table>
            
            <h3>Disabled Trades</h3>
            <table border="1">
                <tr>
                    <th width="80px">Action</th>
                    <th width="80px">Pair</th>
                    <th width="80px">Price</th>
                    <th width="80px">Amount</th>
                    <th>Approx USD</th>
                    <th colspan="2">Edit</th>
                </tr>
                
                <?=$disabledTrades?>
            </table>
        </td>
    </tr>
</table>



<form id="updateForm" title="Update Trade">

    ID <input type="button" name="idButton" />
    <br />
    
    
    <table>
        <tr>
            <td>Pair</td>
            <td> <select name="pair" id="pair">
                    <option value="btc_usd">BTC/USD</option>
                    <option value="ltc_usd">LTC/USD</option>
                    <option value="ltc_btc">LTC/BTC</option>
                    <option value="nvc_usd">NVC/USD</option>
                    <option value="nmc_usd">NMC/USD</option>
                    <option value="ppc_usd">PPC/USD</option>
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
    </select>
    
    
</form>