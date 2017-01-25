<?php
include($dir.'functions.php');
include($dir.'config.php');
include($dir.'ez_sql_core.php');
include($dir.'ez_sql_mysql.php');

///////////////////////////
$tableName = 'credentials';
///////////////////////////

$id = $_REQUEST['id'];

global $db;

$db = new ezSQL_mysql($dbUser, $dbPW, $dbName, $dbHost);



foreach($_REQUEST as $request => $value) {
    $_REQUEST[$request] = mysql_real_escape_string($value);
}

switch($_GET['action']) {
    case 'update':
       
	   $update = "UPDATE $tableName SET name='".$_REQUEST['name']."',
            acct='".$_REQUEST['acct']."',
			priority='".$_REQUEST['priority']."',
            campaign='".$_REQUEST['campaign']."',
			username='".$_REQUEST['username']."',
			email='".$_REQUEST['email']."',
			password='".$_REQUEST['password']."',
			url='".$_REQUEST['url']."',
			referralURL='".$_REQUEST['referralURL']."',
			extra='".$_REQUEST['extra']."'
            WHERE id='".$id."'";
			
        $success = $db->query($update); 
        
        if($success == 1)
            echo 'Updated record '.$id;
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
	
        $insert = "INSERT INTO $tableName (name, acct, priority, campaign, username, email, password, url, referralURL, extra) values (
            '".$_REQUEST['name']."', '".$_REQUEST['acct']."', '".$_REQUEST['priority']."', '".$_REQUEST['campaign']."', '".$_REQUEST['username']."', '".$_REQUEST['email']."', '".$_REQUEST['password']."', '".$_REQUEST['url']."', '".$_REQUEST['referralURL']."', '".$_REQUEST['extra']."'
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