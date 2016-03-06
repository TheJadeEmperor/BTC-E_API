<?php 
class Bitfinex {
    private $api_key;
    private $api_secret;
    private $api_version = 'v1';
    private $base_url = 'https://api.bitfinex.com';
    private $debug = 0;
    private $bitfinexTrading = 1;
    private $isMarginAvailable = 1;
    private $side;
	
    public function __construct($api_key, $api_secret, $api_version = 'v1') {
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;
        $this->api_version = $api_version;
        $this->debug = $_GET['debug']; //debug mode
        $this->isMarginAvailable = $this->isMarginAvailable();
    }
       
    public function new_order($data) { // to add new order
        $data['request']='/'.$this->api_version.'/order/new';

        if($this->debug == 0) { //debug mode is off
            if($this->bitfinexTrading == 1) { //trading is set to on
                if($this->isMarginAvailable == 1) //if margin is available for trading
                    return $this->process_request($data);
                else 
                    return 'No balance to trade';
            }
            else 
                return 'Bitfinex trading is OFF';
        }
        else 
            return 'Debug mode is on';
    }
    
    public function margin_long_pos($symbol, $latestPrice, $posAmt) { // to add new order
        
        $longPosA = array( 
            'symbol' => $symbol,
            'price' => $latestPrice,
            'amount' => "$posAmt",
            'side' => 'buy', //buying side
            'type' => 'limit', //margin buying
            'exchange' => 'bitfinex'
        );
        
        $this->side = 'Buy';
        return $this->new_order($longPosA);
    }
    
    public function margin_short_pos($symbol, $latestPrice, $posAmt) {
        
        $shortPosA = array( 
            'symbol' => $symbol,
            'price' => $latestPrice,
            'amount' => "$posAmt",
            'side' => 'sell', //selling side
            'type' => 'limit', //margin buying
            'exchange' => 'bitfinex'
        );

        $this->side = 'Sell';
        return $this->new_order($shortPosA);
    }
    
    public function active_orders() { // to get active orders
        $data['request']='/'.$this->api_version.'/orders';
        return $this->process_request($data);
    }

    public function margin_infos() { // to get account information
    
        $data['request']='/'.$this->api_version.'/margin_infos';
        return $this->process_request($data);
    }

    public function acc_info() { // to get account information
        $data['request']='/'.$this->api_version.'/account_infos';
        return $this->process_request($data);
    }
    
    public function active_positions() { // to get active positions
        $data['request']='/'.$this->api_version.'/positions';
        return $this->process_request($data);
    }

    public function get_balances() { // to get account information
        $data['request']='/'.$this->api_version.'/balances';
        return $this->process_request($data);
    }

    //check if bitfinex trading is turned on in the db
    public function isBitfinexTrading($bitfinexTrading) {
        $this->bitfinexTrading = $bitfinexTrading;
    }
    
    //check if there's margin available for trading
    public function isMarginAvailable() {
        $acctFunds = $this->get_balances(); 
        $acctMargin = $this->margin_infos(); 

        //funds in margin balance
        $balanceBTC = $acctFunds[4]['amount'];
        $balanceLTC = $acctFunds[5]['amount'];
        $balanceUSD = $acctFunds[8]['amount'];

        $marginUSD = $acctMargin[0]['margin_limits'][0]['tradable_balance'];
        
        if($marginUSD > 1.0) {
            return 1;
        }
        else {
            return 0;
        }
    }
 
    public function get_side() {
        return $this->side;
    }
    
    
    private function process_request($data) {
        $ch = curl_init();
        $url = $this->base_url . $data['request'];

        $headers = $this->prepare_header($data);

        curl_setopt_array($ch, array(
                CURLOPT_URL  => $url,
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_POSTFIELDS => ""
        ));

        if( !$result = curl_exec($ch) )
        {
                return false;
        } 
        else 
        {
                return json_decode($result, true);
        }
    } 		 

    private function send_unsigned_request($request) {
        $ch = curl_init();
        $url = $this->base_url . $request;

        curl_setopt_array($ch, array(
                CURLOPT_URL  => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false
        ));

        if( !$result = curl_exec($ch) )
        {
                return false;
        } 
        else
        {
                return json_decode($result, true);
        }
    }

    private function prepare_header($data) {
        $data['nonce'] = (string) number_format(round(microtime(true) * 10000000000),0,'.','');

        $payload = base64_encode(json_encode($data));
        $signature = hash_hmac('sha384', $payload, $this->api_secret);

        return array(
            'X-BFX-APIKEY: ' . $this->api_key,
            'X-BFX-PAYLOAD: ' . $payload,
            'X-BFX-SIGNATURE: ' . $signature
        );
    }
    
}
?>