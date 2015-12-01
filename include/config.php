<?php
/*
// connect to database, returns resource 
function database($host, $user, $pw, $dbName) {
    global $db; 
    if(is_int(strpos(__FILE__, 'C:\\')))	//connect to database remotely (local server)
    {
        $db = mysql_connect($host, $user, $pw) or die(mysql_error().' ('.__LINE__.')');
    }
    else //connect to database directly (live server)
    { 
        $db = mysql_connect('localhost', $user, $pw) or die(mysql_error().' ('.__LINE__.')');
    }
    mysql_select_db($dbName) or die(mysql_error());

    return $db;
}
*/

global $db; //PDO database connection 
global $context; //DB table names
global $api; //api instance
global $allPrices; //array with all prices

//database info goes here
////////////////////////////////
$dbHost = '74.220.207.187';
$dbUser = 'codegeas_root';
$dbPW = 'KaibaCorp1!';  
$dbName = 'codegeas_trade';   
////////////////////////////////

if(is_int(strpos(__FILE__, 'C:\\'))) { //localhost
    $c = $db = new PDO('mysql:host=74.220.207.187:3306;dbname='.$dbName.';charset=utf8', $dbUser, $dbPW);
}
else { //live website
    $c = $db = new PDO('mysql:host=localhost;dbname='.$dbName.';charset=utf8', $dbUser, $dbPW);
}

$context['tradesTable'] = 'api_trades';
$context['pricesTable'] = 'api_prices';
$context['optionsTable'] = 'api_options';

//$allPrices = array(); 
?>