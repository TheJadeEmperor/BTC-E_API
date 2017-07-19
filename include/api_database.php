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

	
    public function tradesTable() {
		$queryT = "SELECT *, date_format(until, '%m/%d/%Y') as until_date,
		date_format(until, '%H:%i:%s') as until_time,
		date_format(until, '%m/%d %h:%i %p') as until_format
		FROM ".$this->context['tradesTable']." ORDER BY trade_currency, trade_condition";
		
        $resultT = $this->db->get_results($queryT);
		
		return $resultT;
	}
	
	public function alertsTable() {
		
		$queryA = 'SELECT * FROM '.$this->context['alertsTable'].' ORDER BY currency, on_condition';
        
		$resultA = $this->db->get_results($queryA);
		
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