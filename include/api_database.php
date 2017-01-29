<?php
class Database {
    
    private $db;
    private $candle_1;
    private $candle_12;
    private $candle_24;
    private $recorded_ATH;    
    private $recorded_ATL;
    private $currency;
    private $price_field;
    private $context = array(
		'alertsTable' => 'api_alerts',
		'tradesTable' => 'btc_trades',
        'optionsTable' => 'api_options',
    );
    

    public function __construct($db) {
        $this->db = $db; //database connection object
    }
    
    public function get_options($exchange = 'bitfinex') { //get trading options from api_options
        
        $queryO = 'SELECT * FROM '.$this->context['optionsTable'].' ORDER BY opt';
        $resultO = $this->db->query($queryO);

        foreach($resultO as $opt) { 
            $bitfinexOption[$opt['opt']] = $opt['setting'];
        }
        $this->currency = $bitfinexOption['bitfinex_currency'];
        $this->price_field = $exchange.'_'.$this->currency;
        
        return $bitfinexOption;
    }
     
    
	
    public function tradesTable() {
		$queryT = 'SELECT * FROM '.$this->context['tradesTable'].' ORDER BY id';
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

        $mailSent = mail($textTo, 'Bitfinex', $sendEmailBody, $headers);

        if($mailSent) {
            $subject = 'Text alert sent';
        }
        else {
            $subject = 'Text alert NOT sent';
        }
		
		
        mail($emailTo, $subject, $sendEmailBody, $headers);
    }
}

?>