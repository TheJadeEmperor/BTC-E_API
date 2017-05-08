<?php
class Database {
    
    private $db;
    
	private $context = array(
		'alertsTable' => 'btc_alerts',
		'tradesTable' => 'btc_trades',
        'optionsTable' => 'btc_options',
    );
    
    public function __construct($db) {
        $this->db = $db; //database connection object
    }
    
    public function tradesTable() {
		$queryT = "SELECT *, date_format(until, '%m/%d/%Y') as until_date,
		date_format(until, '%H:%i:%s') as until_time  FROM ".$this->context['tradesTable']." ORDER BY trade_currency, trade_condition";
		
        $resultT = $this->db->get_results($queryT);
		
		return $resultT;
	}
	
	public function alertsTable() {
		
		$queryA = 'SELECT * FROM '.$this->context['alertsTable'].' ORDER BY currency, on_condition';
        
		$resultA = $this->db->get_results($queryA);
		
		return $resultA;
	}
	
    public function sendMail($sendEmailBody) {
		global $emailTo;
		global $textTo;
		
        $headers = 'From: alerts@bestpayingsites.com' . "\r\n" .
        'Reply-To: alerts@bestpayingsites.com' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();

        $mailSent = mail($textTo, 'Alert', $sendEmailBody, $headers);

		$error = error_get_last();
		echo $error["message"];
		
        if($mailSent) {
            $subject = 'Text alert sent';
        }
        else {
            $subject = 'Text alert NOT sent';
        }
		
		
        mail($emailTo, $subject, $sendEmailBody, $headers);
		
		$error = error_get_last();
		echo $error["message"];
    }
	
	/*
	Show emails for testing purposes
	*/
	public function showMail() {
		global $emailTo;
		global $textTo;
		echo $emailTo.' '.$textTo;
	}
}

?>