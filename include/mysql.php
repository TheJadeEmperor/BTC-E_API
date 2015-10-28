<?php
/*
 * database($host, $user, $pw, $dbName)
 * connects to database, returns connection resource
 * 
 * stripAllSlashes($array)
 * 
 */

// connect to database, returns resource 
function database($host, $user, $pw, $dbName) {
    global $conn; 
    if(is_int(strpos(__FILE__, 'C:\\')))	//connect to database remotely (local server)
    {
        $conn = mysql_connect($host, $user, $pw) or die(mysql_error().' ('.__LINE__.')');
    }
    else //connect to database directly (live server)
    { 
        $conn = mysql_connect('localhost', $user, $pw) or die(mysql_error().' ('.__LINE__.')');
    }
    mysql_select_db($dbName) or die(mysql_error());

    return $conn;
}


function stripAllSlashes($array)
{
    foreach($array as $fld => $val)
    {
        $newArray[$fld] = stripslashes($val);
    }
    return $newArray;
}
?>