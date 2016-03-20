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
        'tradeDataTable' => 'api_trade_data',
        'optionsTable' => 'api_options',
        'pricesTable2h' => 'api_prices',
        'pricesTable30m' => 'api_prices_30m',
        'balanceTable' => 'api_balance'
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
    
    public function get_candles ($price_field) {

        $candle_1_to_12 = array(); //prices of candles 1 to 24 
        
        //get prices for 70 candles (35 hours)
        $queryT = 'SELECT * FROM '.$this->context['pricesTable2h'].' WHERE count <= 70 ORDER BY count desc';
        $resultT = $this->db->query($queryT);

        foreach($resultT as $row) { 
            if($row['count'] == 1) {
                $this->candle_1 = $row[$price_field]; //most recent candle
            }
            else if($row['count'] == 12) {
                $this->candle_12 = $row[$price_field]; //least recent candle
            }
          
            //get the ATH and ATL for 24 candles
            if($row['count'] <= 12) {
                array_push($candle_1_to_12, $row[$price_field]);
            }
        }

        $this->recorded_ATL = min($candle_1_to_12);
        $this->recorded_ATH = max($candle_1_to_12);
        
        return $this->db->query($queryT);
    }
 
    
    public function get_percent_diff () {
        $candle_1 = $this->candle_1;
        $candle_12 = $this->candle_12;
        
        //price diff between 1st candle and last candle
        $diff = $this->candle_1 - $this->candle_12;

        //determine if trend is pump or dump
        $percentDiff =  $diff/$this->candle_1 * 100;
        
        return $percentDiff;
    }
    
    public function get_recorded_ATH () {
        return $this->recorded_ATH;
    }
    
    public function get_recorded_ATL () {
        return $this->recorded_ATL;
    }
    
    //get most recent exponential moving average - EMA
    public function get_ema ($range = 10) { //pass in the range - # of candles
        $R = $range;
        $dbArray = $this->get_candles('bitfinex_btc');
        $k = 2/($R + 1); //smoothing constant
        
        $array = array();
        $arrayPrice = array();
        foreach($dbArray as $row) {
            array_push($array, $row);
            array_push($arrayPrice, $row['bitfinex_btc']);
        }
        
        $smaR = $emaR = 0;
        for($count = 1; $count < 70; $count++) {//go through all candles
            
            $bitfinex_btc = $array[$count]['bitfinex_btc'];
            
            if($count >= $R) { 
                //get only last R prices from array
                $lastR = array_slice($arrayPrice, $count-$R, $R, true);
                $smaR = array_sum ($lastR)/$R; //average of R prices = SMA

                if($emaR == 0) $emaR = $smaR; 
                else $emaR = $k * ($bitfinex_btc - $emaR) + $emaR; //formula for EMA
            }

            $smaR = number_format($smaR, 2);
            $emaR = number_format($emaR, 2);
           // $sma21 = number_format($sma21, 2);
           // $ema21 = number_format($ema21, 2); 
        }
        
        return $emaR;
    }
    
    
    public function isGreen ($candleN) { //did the price increase from 1 candle ago
        $price_field = $this->price_field;
        
        $queryT = 'SELECT * FROM '.$this->context['pricesTable30m'].' WHERE count = "'.$candleN.'" LIMIT 1';
        $resultT = $this->db->query($queryT);

        foreach($resultT as $p1) { $first = $p1[$price_field]; }
        
        $queryT = 'SELECT * FROM '.$this->context['pricesTable30m'].' WHERE count = "'.($candleN+1).'" LIMIT 1';
        $resultT = $this->db->query($queryT);
        
        foreach($resultT as $p2) { $second = $p2[$price_field]; }
        
        $diff = $first - $second; 
        
        //echo ' '.$diff;
        if($diff > 0) { //price rose
            return 1;
        }
        else if ($diff < 0) { //price dropped
            return 0;
        }
        else { //price stayed the same
            return -1;
        }
    }
    
    /*
    Updates the last action the program performed - buy or sell 
     * $last_action = array(
     *      last_action - buy or sell
     *      last_price - price of trade
     *      trade_signal - rest or stop loss
     *      currency - ltc or btc
     *      exchange - btc-e or bitfinex
     *      last_updated - timestamp
     * )
    */
    public function update_last_action($last_action_data) {

        $context = $this->context;
        
        $queryD = 'INSERT INTO '.$context['tradeDataTable'].' SET 
            last_action="'.$last_action_data['last_action'].'",
            last_price="'.$last_action_data['last_price'].'",            
            trade_signal="'.$last_action_data['trade_signal'].'",
            last_updated="'.date('Y-m-d H:i:s', time()).'",
            currency="'.$last_action_data['currency'].'",
            exchange="bitfinex"';
    
        $this->db->query($queryD);
    }
    
    public function sendMail($sendEmailBody) {
        $headers = 'From: alerts@bestpayingsites.com' . "\r\n" .
        'Reply-To: alerts@bestpayingsites.com' . "\r\n" .
        'X-Mailer: PHP/' . phpversion();

        $emailTo = '17182136574@tmomail.net';
        $mailSent = mail($emailTo, 'Bitfinex', $sendEmailBody, $headers);

        if($mailSent) {
            $subject = 'Text alert sent';
        }
        else {
            $subject = 'Text alert NOT sent';
        }

        $emailTo = 'louie.benjamin@gmail.com'; 
        mail($emailTo, $subject, $sendEmailBody, $headers);
    }
}

?>