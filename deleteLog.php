<?
$dir = 'include/';
include($dir.'api_database.php');
include($dir.'functions.php');
include($dir.'config.php');

//debug mode only
$server = $_SERVER['SERVER_NAME'];
if ($server == 'localhost' || $server == 'btcAPI.test') {
    $localHost = 'http://localhost/btcAPI/';
}
else {
    echo 'Invalid Request';
    exit;
}


$id = $_GET['id'];

if($_POST['delete']) {
    $database = new Database($conn);

    $message = $database->deleteLog($id);
    $dis = 'disabled';
    echo 'id: '.$id.' | message: '.$message;
}
?>
<form method="POST">
<input type="submit" value="Delete Record <?=$id?>" name="delete" <?=$dis?> />
</form>