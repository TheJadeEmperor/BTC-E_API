<?php
include($dir.'config.php');
include($dir.'ez_sql_core.php');
include($dir.'ez_sql_mysql.php');


$id = $_REQUEST['id'];

global $db;

$db = new ezSQL_mysql($dbUser, $dbPW, $dbName, $dbHost);



foreach($_REQUEST as $request => $value) {
    $_REQUEST[$request] = mysql_real_escape_string($value);
}

switch($_GET['action']) {
    case 'update':
       
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
        
    case 'delete':
	
        $success = $db->query("DELETE from $tableName WHERE id='".$id."'");
        
        if($success == 1) 
            echo 'Successfully deleted record '.$id;
        else
            echo 'Failed to delete record '.$id;
        break;
        
    case 'create':
	
        $insert = "INSERT INTO $tableName (currency, on_condition, price, unit, exchange) values (
            '".$_REQUEST['currency']."', '".$_REQUEST['on_condition']."', '".$_REQUEST['price']."', '".$_REQUEST['unit']."', '".$_REQUEST['exchange']."' 
        )";
        
        $success = $db->query($insert);
        
        if($success == 1) 
            echo 'Added record '.$insert;
        else 
            echo 'Failed to add record '.$insert;
        
        break;
		
    case 'read':
    default:
        $news = $db->get_row("SELECT * FROM $tableName WHERE id='".$id."'");

        echo json_encode($news);
        break;
}


?>