<?php
include($dir.'config.php');
include($dir.'ez_sql_core.php');
include($dir.'ez_sql_mysql.php');

//error_reporting(0);


$id = $_REQUEST['id'];
$trade_id = $_REQUEST['trade_id'];

global $db;

$db = new ezSQL_mysql($dbUser, $dbPW, $dbName, $dbHost);


foreach($_REQUEST as $request => $value) {
    $_REQUEST[$request] = mysql_real_escape_string($value);
}


//handle date and time field
$until_date = date('Y-m-d', strtotime($_REQUEST['until_date']));
$until = $until_date.' '.$_REQUEST['until_time'];


switch($_GET['action']) { 
 
	case 'createTrade':
		$insert = "INSERT INTO $tradeTable (trade_exchange, trade_currency, trade_condition, trade_price, trade_action, trade_amount, trade_unit, until) values ('".$_REQUEST['trade_exchange']."', '".$_REQUEST['trade_currency']."', '".$_REQUEST['trade_condition']."', '".$_REQUEST['trade_price']."', '".$_REQUEST['trade_action']."', '".$_REQUEST['trade_amount']."', '".$_REQUEST['trade_unit']."', '".$until."' )";
        
        $success = $db->query($insert);
        if($success == 1) 
            echo 'Added record '.$insert;
        else 
            echo 'Failed to add record '.$insert;

		break;
	
	case 'readTrade':
		//format 2 fields for date time - until_date - until_time
		$readQuery = "SELECT *, date_format(until, '%m/%d/%Y') as until_date,
		date_format(until, '%H:%i:%s') as until_time
		FROM $tradeTable WHERE id='".$trade_id."'";
		
        $result = $db->get_row($readQuery);

        echo json_encode($result);

		break;
		
	case 'updateTrade':

	   $update = "UPDATE $tradeTable SET 
			trade_exchange = '".$_REQUEST['trade_exchange']."',
			trade_currency = '".$_REQUEST['trade_currency']."',
            trade_condition = '".$_REQUEST['trade_condition']."',
			trade_price = '".$_REQUEST['trade_price']."',
			trade_action = '".$_REQUEST['trade_action']."',
            trade_amount = '".$_REQUEST['trade_amount']."',
			trade_unit = '".$_REQUEST['trade_unit']."',
			result = '".$_REQUEST['result']."',
			until = '".$until."'
            WHERE id = '".$trade_id."'";
			
        $success = $db->query($update); 
        
        if($success == 1)
            echo 'Updated record '.$trade_id.' '.$update;
        else 
            echo 'Failed to update record '.$update;
        break;
		
	case 'deleteTrade':

        $success = $db->query("DELETE from $tradeTable WHERE id='".$trade_id."'");
        
        if($success == 1) 
            echo 'Successfully deleted record '.$trade_id;
        else
            echo 'Failed to delete record '.$trade_id;
        break;
		
	
	case 'update': //updateAlert
       
	   $update = "UPDATE $tableName SET currency = '".$_REQUEST['currency']."',
            on_condition = '".$_REQUEST['on_condition']."',
			price = '".$_REQUEST['price']."',
            unit = '".$_REQUEST['unit']."',
			exchange = '".$_REQUEST['exchange']."',
			sent = '".$_REQUEST['sent']."'
            WHERE id = '".$id."'";
			
        $success = $db->query($update); 
        
        if($success == 1)
            echo 'Updated record '.$id.' '.$update;
        else 
            echo 'Failed to update record '.$update;
        break;
        
    case 'delete':	//deleteAlert
	
        $success = $db->query("DELETE from $tableName WHERE id='".$id."'");
        
        if($success == 1) 
            echo 'Successfully deleted record '.$id;
        else
            echo 'Failed to delete record '.$id;
        break;
        
    case 'create':	//createAlert
	
        $insert = "INSERT INTO $tableName (currency, on_condition, price, unit, exchange) values (
            '".$_REQUEST['currency']."', '".$_REQUEST['on_condition']."', '".$_REQUEST['price']."', '".$_REQUEST['unit']."', '".$_REQUEST['exchange']."' )";
        
        $success = $db->query($insert);
        
        if($success == 1) 
            echo 'Added record '.$insert;
        else 
            echo 'Failed to add record '.$insert;
        
        break;
		
    case 'read':	//readAlert
		$result = $db->get_row("SELECT * FROM $tableName WHERE id='".$id."'");

        echo json_encode($result);
        break;
	
	case 'updateNotes': 
	
		$update = "UPDATE $optionsTable SET setting=\"".$_REQUEST['notes']."\" WHERE opt='notes'";
			
        $success = $db->query($update); 
        
        if($success == 1)
            echo 'Updated notes: '.$_REQUEST['notes'];
        else 
            echo 'Failed to update notes: '.$_REQUEST['notes'];
        break;
   
}


?>