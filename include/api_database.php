<?php
class Database {
    
    private $db;
    
	private $context = array(
		'alertsTable' => 'btc_alerts',
		'tradesTable' => 'btc_trades',
        'optionsTable' => 'btc_options',
		'logTable' => 'btc_log'
    );
    
    public function __construct($db) {
        $this->db = $db; //database connection object
    }
	
	
	// connect to database, returns resource 
	public function database($host, $user, $pw, $dbName) {
		global $conn;
		
		if(is_int(strpos(__FILE__, 'C:\\'))) {	//connect to database remotely (local server) 
		 
			$conn = mysqli_connect($host, $user, $pw) or die(mysqli_error().' ('.__LINE__.')');
		}
		else { //connect to database directly (live server)
			$conn = mysqli_connect('localhost', $user, $pw) or die(mysqli_error().' ('.__LINE__.')');
		}
	
		mysqli_select_db($dbName);

		$this->db = $conn;

		return $conn;
	}

	public function getLogs($exchange) {

		$selQuery = 'SELECT *, DATE_FORMAT(recorded, "%Y-%m-%d %h:%i:%s") as recorded FROM '.$this->context['logTable'].' WHERE exchange="'.$exchange.'" ORDER BY id desc';
		$result = $this->db->query($selQuery);

		if($_GET['debug'] == 1) {
			echo '<pre>'.$selQuery.'</pre>';
		}
	
		if( FALSE === $result ) {
			$message = 'Query failed: '.$selQuery;
		} 
		else { 
			return $result;
		}

	}

	public function deleteLog($logID) {
		//delete from db 
		$delQ = 'DELETE FROM btc_log WHERE id="'.$logID.'" LIMIT 1';
		$result = $this->db->query($delQ);

		if( FALSE === $result ) {
			$message = 'Failed to delete log: '.$delQ;
		} 
		else {
			$message = 'Successfully deleted log '.$id;
		}
		return $message;
	}
    
	public function format_percent_display($percent_number) {
		$percent_number = number_format($percent_number, 2).'%';
		
		if($percent_number > 0) {
			$percent_number = '<span class="green">+'.$percent_number.'</span>';
		} 
		else{
			$percent_number = '<span class="red">'.$percent_number.'</span>';		
		}
		
		return $percent_number;
	}

	public function format_change_display ($number, $decimal) {
		$number = number_format($number, $decimal);
		
		if($number > 0) {
			$number = '<span class="green">+'.$number.'</span>';
		} 
		else{
			$number = '<span class="red">'.$number.'</span>';		
		}
		
		return $number;
	}

	 
	function coinbasePrice ($currencyPair) {
	
		$url = 'https://api.coinbase.com/v2/prices/'.$currencyPair.'/spot';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, $url);
		$result=curl_exec($ch);
		curl_close($ch);

		$decode = json_decode($result, true);
		
		return $decode['data']['amount'];
	}

	//future: delete this function
    public function tradesTable() {
		$queryT = "SELECT *, date_format(until, '%m/%d/%Y') as until_date,
		date_format(until, '%H:%i:%s') as until_time,
		date_format(until, '%m/%d %h:%i %p') as until_format
		FROM ".$this->context['tradesTable']." ORDER BY trade_currency, trade_condition";
		
        $resultT = $this->db->get_results($queryT);
		
		return $resultT;
	}
	
	 public function getTrades () {
		$queryT = "SELECT *, date_format(until, '%m/%d/%Y') as until_date,
		date_format(until, '%H:%i:%s') as until_time,
		date_format(until, '%m/%d %h:%i %p') as until_format
		FROM ".$this->context['tradesTable']." ORDER BY trade_currency, trade_condition";
		
        
		$this->db->query($queryT);
		
		$resultT = $this->db->get();
		
		return $resultT;
	}
	
	//future: delete this function
	public function alertsTable() {
		
		$queryA = 'SELECT * FROM '.$this->context['alertsTable'].' ORDER BY currency, on_condition';
        
		$resultA = $this->db->get_results($queryA);
		
		return $resultA;
	}
	
	
	public function getAlerts ($debug) {
		
		$queryA = 'SELECT * FROM '.$this->context['alertsTable'].' ORDER BY currency, on_condition';
        
		$this->db->query($queryA);
		
		$resultA = $this->db->get();

		if($debug == 1) {
			echo '<br />'.$queryA.'<br />'; 
			print "<pre>";
			print_r($resultA);
			print "</pre>";
		}
		
		return $resultA;
	}
	
	
	public function getSettingsFromDB() {
		
		$queryO = 'SELECT * FROM '.$this->context['optionsTable'].' ORDER BY opt, setting';
        
		$resultO = $this->db->get_results($queryO);
		
		return $resultO;
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