<?php
require_once __DIR__.'/include/api_imap_class.php';

/**
 *  Important: Goto the gmail and make less secure toggle button to off so imap will able to fetch emails
 *  Enter: email and password in relevent fields below
 *  if maximum execution time issue occurs uncomment the below init_set function to increase max execution time
 */

/**
 * Creating connection by calling constructor of EmailImporter class
 */

//ini_set('max_execution_time', 300);
//$mails = new EmailImporter( '{imap.gmail.com:993/imap/ssl}INBOX','louie.benjamin@gmail.com','CCCccc333###');

/**
 * Printing RESULTS here
 * Important: Try to fetch plan emails so you will get clean objects otherwise ragex functions of php will use to clean the emails
 * Possible getter methods are
 * 1. getMailsReceivedFrom
 * 2. getMailsSentTo
 * 3. getMailsBySubject
 * 4. getMailsByKeyword
 * 5. getMailsUnseen
 * 6. getAllEmails
 */

echo"<pre>";
//print_r($mails->getMailsBySubject("botje11 that you follow published a new idea"));


//ini_set('max_execution_time', 300);
//$mails = new EmailImporter( '{imap.gmail.com:993/imap/ssl}INBOX','kaiba.online.acc@gmail.com','PhoenixSaint1!');


$mails = new EmailImporter( '{imap.gmail.com:993/imap/ssl}INBOX','louie.online.acc@gmail.com','PhoenixSaint1!');


//print_r($mails->getMailsUnseen(''));


$subjectSignalLong = "TradingView Alert: Long Signal";
$subjectSignalShort = "TradingView Alert: Short Signal";

$matchedMailsLong = $mails->getMailsBySubject($subjectSignalLong);
$matchedMailsShort = $mails->getMailsBySubject($subjectSignalShort);

print_r($mails->getMailsBySubject($subjectSignalShort));



if($matchedMailsLong[0]['subject'] == $subjectSignalLong) { 
	echo 'long '; 
}

if($matchedMailsShort[0]['subject'] == $subjectSignalShort) { 
	echo 'short'; 
}





?>