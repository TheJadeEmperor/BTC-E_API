<?php
include('ez_sql_core.php');
include('ez_sql_mysql.php');
include('config.php');

$id = $_REQUEST['id'];

foreach($_REQUEST as $request => $value) { 
    $_REQUEST[$request] = mysql_real_escape_string($value);
}

switch($_GET['action']) {
    case 'update':
        $update = "UPDATE api_trades SET 
            status='".$_REQUEST['status']."',
            pair='".$_REQUEST['pair']."', 
            action='".$_REQUEST['action']."',
            price='".$_REQUEST['price']."', 
            amount='".$_REQUEST['amount']."'
            WHERE id='".$id."'";
        $success = $db->query($update);
        
        if($success == 1)
            echo 'Updated record '.$id;
        else 
            echo 'Failed to update record '.$id;
        break;
        
    case 'delete':
        $success = $db->query("DELETE from api_trades WHERE id='".$id."'");
        
        if($success == 1) 
            echo 'Successfully deleted record '.$id;
        else
            echo 'Failed to delete record '.$id;
        break;
                           
        
    case 'create':
        $insert = "INSERT INTO api_trades (status, pair, action, price, amount) values (
            '".$_REQUEST['status']."', '".$_REQUEST['pair']."', '".$_REQUEST['action']."', '".$_REQUEST['price']."', '".$_REQUEST['amount']."'
        )";
        
        $success = $db->query($insert);
        
        if($success == 1) 
            echo 'Added record '.$insert;
        else 
            echo 'Failed to add record '.$insert;
        
        break;
    
    case 'read':
    default:
        $allTrades = $db->get_row("SELECT * FROM api_trades WHERE id='".$id."'");

        echo json_encode($allTrades);
        break;
}


?>