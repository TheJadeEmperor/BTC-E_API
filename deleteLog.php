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

$database = new Database($conn);

$id = $_GET['id'];

$message = $database->deleteLog($id);

echo 'id: '.$id.' | message: '.$message;

?>