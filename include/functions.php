<?php
// connect to database, returns resource 
function database($host, $user, $pw, $dbName) {

	if(!is_int(strpos(__FILE__, 'C:\\'))) { //connect to db remotely (local server)
		$host = 'localhost';
	}
	
	$conn = new mysqli($host, $user, $pw, $dbName);
	// Check connection
	if ($conn -> connect_errno) {
	  echo __LINE__." ". $conn -> connect_error;
	  exit();
	}

	return $conn;
}

function shortenText($text, $limit) {
	//$limit = number of characters you want to display
	$new = $text;
	$new = substr($new, 0, $limit);
	
	if(strlen($text) > $limit)
		$new = $new.'...';
	return $new;
}//function

//format mysql fields
function formatFields($row) {
    foreach($row as $fld => $val) {
        $val = stripslashes($val);
        $row[$fld] = trim($val); 
    }
    return $row; 
}

function randomChar() {
	$letters = array(1 => "a", "b", "c", "d", "e", "f", "g", "h" ,"i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z","A", "B", "C", "D", "E", "F", "G", "H" ,"I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z","0","1","2","3","4","5","6","7","8","9");
	$index = Key($letters);
	$element = Current($letters);
	$index = rand(1,62);
	$random_letter = $letters[$index];
	return $random_letter;
}

//create random hash
function genString($number) {
	for ($i = 0; $i < $number; $i++) {
	    $hash = $hash.(randomChar());
	}
	return $hash;
}


function stripAllSlashes($array) {
    foreach($array as $fld => $val) {
        $newArray[$fld] = stripslashes($val);
    }
    return $newArray;
}

/*
$opt = array(
 	'tableName' => $tableName,
 	'dbFields' => array(
 		'fld' => $val)
	 );
*/
function dbInsert($opt) {
	global $conn; 
	
	$fields = $values = array();
	foreach($opt['dbFields'] as $fld => $val) {
		array_push($fields, $fld);
		
		if($val == 'now()') //mysql timestamp
			array_push($values, $val); 
		else
			array_push($values, '"'.addslashes($val).'"');
	}
	
	$theFields = implode(',', $fields);
	$theValues = implode(',', $values);
	
	$ins = 'INSERT INTO '.$opt['tableName'].' ('.$theFields.') VALUES ('.$theValues.')';
	$res = $conn->query($ins);

	if($_GET['debug'] == 1) {
		echo '<pre>'.$ins.'<br />insert_id:'.$conn->insert_id.'</pre>';
	}
	
	return $res;
}

/*
 * $opt = array(
 * 	'tableName' => $tableName,
 * 	'cond' => $cond)
 * */
function dbSelect($opt) {
	global $conn; 
	
	$sel = 'SELECT * FROM '.$opt['tableName']; 
	
	if($opt['cond'])
		$sel .= ' '.$opt['cond']; 
	
	$res = $conn->query($sel);

	while($rows = $res->fetch_array()) {
		foreach($rows as $fld => $val) {	//remove slashes 
			$rows[$fld] = stripslashes($val);  
		}
		$mysql[] = $rows;		
	}

	if($_GET['debug'] == 1) {
		echo '<pre>'.$sel.'</pre>';
	}
	
	return $mysql;
}


/*
$opt = array(
	'tableName' => $tableName,
	'cond' => $cond
);
 */
function dbSelectQuery($opt) {
	global $conn; 
	
	$sel = 'SELECT * FROM '.$opt['tableName']; 
	
	if($opt['cond'])
		$sel .= ' '.$opt['cond']; 

	$res = $conn->query($sel);

	if($_GET['debug'] == 1) {
		echo '<pre>'.$sel.'</pre>';
	}

	return $res;
}

/*
$opt = array(
	'tableName' => $tableName,
	'cond' => $cond)
 */
function dbDeleteQuery ($opt) {
	global $conn; 
	$delQ = 'DELETE FROM '.$opt['tableName']; 

	if($opt['cond']) 
		$delQ .= ' '.$opt['cond'];
	else 
		$delQ .= ' LIMIT 1'; //do not delete everything

	$delR = $conn->query($delQ);

	if($_GET['debug'] == 1) {
		echo '<pre>'.$delQ.'</pre>';
	}
	return $delR;
}

/*
$opt = array(
 	'tableName' => $tableName, 
 	'dbFields' => array(
 		'fld' => $val),
 	'cond' => $cond
);
*/
function dbUpdate($opt) {
	global $context; 
	global $conn;
	
	if(!isset($opt['cond']))
		$opt[cond] = 'limit 1'; //prevent updating of all entries 
	
	$set = array(); 
	foreach($opt['dbFields'] as $fld => $val) {
		array_push($set, $fld.'="'.addslashes($val).'"'); 
	}
	
	$theSet = implode(',', $set); 
	
	$upd = 'UPDATE '.$opt['tableName'].' SET '.$theSet.' '.$opt[cond]; 
	$res = $conn->query($upd);
	
	if($_GET['debug'] == 1) {
		echo '<pre>'.$upd.'</pre>';
	}
	return $res;  
}


function get_ip_address() {
    foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
        if (array_key_exists($key, $_SERVER) === true){
            foreach (explode(',', $_SERVER[$key]) as $ip){
                $ip = trim($ip); // just to be safe

                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                    return $ip;
                }
            }
        }
    }
}

?>