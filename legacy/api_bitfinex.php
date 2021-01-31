<?php 

class Bitfinex {
	private $api_key;
	private $api_secret;
	private $api_version;
	private $base_url = 'https://api.bitfinex.com';
	
	public function __construct($api_key, $api_secret, $api_version = 'v1')
	{
		$this->api_key = $api_key;
		$this->api_secret = $api_secret;
		$this->api_version = $api_version;
	}
	
	public function get_trades()
	{
        $payload = array (
        "limit_bids" => 1,
        "limit_asks" =>  1
        );
        $data = array(
        'url' => 'https://api.bitfinex.com/v1/taken_funds',
        'qs' => $payload
        );
        
		//https://api.bitfinex.com/v1/pubticker/btcusd
		return $this->send_signed_request($data);
	}
	
	public function get_offers()
	{
		$request = '/' . $this->api_version . '/offers';
		
		$data = array(
			'request' => $request,
			'options' => array()
		);
		
		return $this->send_signed_request($data);
	}
	
	public function get_credits()
	{
		$request = '/' . $this->api_version . '/credits';
	
		$data = array(
			'request' => $request,
			'options' => array()
		);
		
		return $this->send_unsigned_request($data);
	}
	
	public function get_balances()
	{
		$request = '/' . $this->api_version . '/balances';
		
		$data = array(
			'request' => $request,
			'options' => array()
		);
		
		return $this->send_signed_request($data);
	}
	
	public function get_history($currency, $limit = 1, $wallet = 'trading')
	{
		$request = '/' . $this->api_version . '/history';
		
		$data = array(
			'request' => $request,
			'currency' 	=> $currency,
			'limit' => $limit,
			'wallet' => $wallet
		);
			
		return $this->send_signed_request($data);
	}
	
        public function get_ticker($symbol){
            $request = '/'.$this->api_version.'/symbols';
            
            $data = array(
                'request' => $request,
                'options' => array()
            );

            return $this->send_signed_request($data);
        }
        
	public function make_trade($amount, $symbol = "btcusd", $type, $price)
	{
		$request = '/'.$this->api_version.'/order/new';
		
                if($type == 'buy') {
                    $side = 'buy';
                    $type = 'exchange limit';
                }
                else if($type == 'sell') {
                    $side = 'sell'; 
                    $type = 'exchange limit';
                }
                else  if($type == 'short') { 
                    $side = 'sell'; 
                    $type = 'market';
                }
                
		$data = array(
                    'request' => $request,
                    'symbol' => $symbol,
                    'amount' => $amount,
                    'price' => $price,
                    'exchange' => 'bitfinex',
                    'side' => $side,
                    'type' => $type
		);
		
		return $this->send_signed_request($data);
	}
        
        
        public function new_order($data)
	{
            $data['request']='/'.$this->api_version.'/order/new';
            return $this->send_signed_request($data);
	}
        
        public function get_positions($data) {
            $data['request']='/'.$this->api_version.'/positions';
            return $this->send_signed_request($data);
        }

        public function claim_position($data) {
            $data['request']='/'.$this->api_version.'/position/claim';
            return $this->send_signed_request($data);
        }

        
	private function prepare_header($data)
	{
		$data['nonce'] = (string) number_format(round(microtime(true) * 100000),0,'.','');
		
		$payload = base64_encode(json_encode($data));
		$signature = hash_hmac('sha384', $payload, $this->api_secret);
		
		return array(
			'X-BFX-APIKEY: ' . $this->api_key,
			'X-BFX-PAYLOAD: ' . $payload,
			'X-BFX-SIGNATURE: ' . $signature
		);
	}
	
	private function send_signed_request($data)
	{
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
	
	private function send_unsigned_request($request)
	{
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
}

?>