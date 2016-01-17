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

	private function prepare_header($data)
	{
		$data['nonce'] = (string) number_format(round(microtime(true) * 10000000000),0,'.','');
		
		$payload = base64_encode(json_encode($data));
		$signature = hash_hmac('sha384', $payload, $this->api_secret);
		
		return array(
			'X-BFX-APIKEY: ' . $this->api_key,
			'X-BFX-PAYLOAD: ' . $payload,
			'X-BFX-SIGNATURE: ' . $signature
		);
	}

	public function new_order($data)   // to add new order
	{
		$data['request']='/'.$this->api_version.'/order/new';
		return $this->process_request($data);
	}

	public function active_orders()		// to get active orders
	{
		$data['request']='/'.$this->api_version.'/orders';
		return $this->process_request($data);
	}

	public function acc_info()		// to get account information
	{
		$data['request']='/'.$this->api_version.'/account_infos';
		return $this->process_request($data);
	}

	public function active_positions()		// to get active positions
	{
		$data['request']='/'.$this->api_version.'/positions';
		return $this->process_request($data);
	}

	public function claim_positions($data)		// to get claim positions
	{
		$data['request']='/'.$this->api_version.'/position/claim';   
		return $this->process_request($data);
	}

	private function process_request($data)
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