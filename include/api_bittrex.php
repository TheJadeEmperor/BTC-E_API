<?php
class Client {
	private $baseUrl;
	private $apiVersion = 'v1.1';
	private $apiKey;
	private $apiSecret;

	public function __construct ($apiKey, $apiSecret) {
		$this->apiKey    = $apiKey;
		$this->apiSecret = $apiSecret;
		$this->baseUrl   = 'https://bittrex.com/api/'.$this->apiVersion.'/';
	}

	/**
	 * Invoke API
	 * @param string $method API method to call
	 * @param array $params parameters
	 * @param bool $apiKey  use apikey or not
	 * @return object
	 */
	private function call ($method, $params = array(), $apiKey = false) {
		$uri  = $this->baseUrl.$method;

		if ($apiKey == true) {
			$params['apikey'] = $this->apiKey;
			$params['nonce']  = time();
		}

		if (!empty($params)) {
			$uri .= '?'.http_build_query($params);
		}

		$sign = hash_hmac ('sha512', $uri, $this->apiSecret);

		$ch = curl_init ($uri);
		curl_setopt ($ch, CURLOPT_HTTPHEADER, array('apisign: '.$sign));
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);

		$answer = json_decode($result);

		if ($answer->success == false) {
			
		}
		echo ($answer->message);

		return $answer->result;
	}

	/**
	 * Get the open and available trading markets at Bittrex along with other meta data.
	 * @return array
	 */
	public function getMarkets () {
		return $this->call ('public/getmarkets');
	}

	/**
	 * Get all supported currencies at Bittrex along with other meta data.
	 * @return array
	 */
	public function getCurrencies () {
		return $this->call ('public/getcurrencies');
	}

	/**
	 * Get the current tick values for a market.
	 * @param string $market	literal for the market (ex: BTC-LTC)
	 * @return array
	 */
	public function getTicker ($market) {
		return $this->call ('public/getticker', array('market' => $market));
	}

	/**
	 * Get the last 24 hour summary of all active exchanges
	 * @return array
	 */
	public function getMarketSummaries () {
		return $this->call ('public/getmarketsummaries');
	}

	/**
	 * Get the last 24 hour summary of all active exchanges
	 * @param string $market literal for the market (ex: BTC-LTC)
	 * @return array
	 */
	public function getMarketSummary ($market) {
		return $this->call ('public/getmarketsummary', array('market' => $market));
	}

	/**
	 * Get the orderbook for a given market
	 * @param string $market  literal for the market (ex: BTC-LTC)
	 * @param string $type	  "buy", "sell" or "both" to identify the type of orderbook to return
	 * @param integer $depth  how deep of an order book to retrieve. Max is 50.
	 * @return array
	 */
	public function getOrderBook ($market, $type, $depth = 20) {
		$params = array (
			'market' => $market,
			'type'   => $type,
			'depth'  => $depth
		);
		return $this->call ('public/getorderbook', $params);
	}

	/**
	 * Get the latest trades that have occured for a specific market
	 * @param string $market  literal for the market (ex: BTC-LTC)
	 * @param integer $count  number of entries to return. Max is 50.
	 * @return array
	 */
	public function getMarketHistory ($market, $count = 20) {
		$params = array (
			'market' => $market,
			'count'  => $count
		);
		return $this->call ('public/getmarkethistory', $params);
	}

	/**
	 * Place a limit buy order in a specific market. 
	 * Make sure you have the proper permissions set on your API keys for this call to work
	 * @param string $market  literal for the market (ex: BTC-LTC)
	 * @param float $quantity the amount to purchase
	 * @param float $rate     the rate at which to place the order
	 * @return array
	 */
	public function buyLimit ($market, $quantity, $rate) {
		$params = array (
			'market'   => $market,
			'quantity' => $quantity,
			'rate'     => $rate
		);
		return $this->call ('market/buylimit', $params, true);
	}

	/**
	 * Place a limit sell order in a specific market. 
	 * Make sure you have the proper permissions set on your API keys for this call to work
	 * @param string $market  literal for the market (ex: BTC-LTC)
	 * @param float $quantity the amount to sell
	 * @param float $rate     the rate at which to place the order
	 * @return array
	 */
	public function sellLimit ($market, $quantity, $rate) {
		$params = array (
			'market'   => $market,
			'quantity' => $quantity,
			'rate'     => $rate
		);
		return $this->call ('market/selllimit', $params, true);
	}

	/** 
	 * @param string $data 
	 * 	$data['market'] 
	 * 	$data['balance'] 
	 * 	$data['orderBookType'] 
	 * @return $output
	 */
	public function useOrderBook ($market, $amt, $dataAction) {

		if($dataAction == 'buy')  
			$orderBookType = 'sell'; //get order type for order book
		else 
			$orderBookType = 'buy'; //orderBookType is opposite of order type

		//get orderbook getOrderBook ($market, $type, $depth) 
		$getOrderBook = $this->getOrderBook ($market, $orderBookType, 10);

		foreach($getOrderBook as $orderBook) {
			$rate = $orderBook->Rate; //rate from orderbook 

			var_dump($orderBook); //view current orderbook

			$QT = $amt; //quantity of coin to buy or sell

			if ($orderBook->Quantity <= $QT) //if orderbook has less than QT 
				$QT = $orderBook->Quantity; //set the QT to what's available

			if($amt > 0) { //if there is balance remaining
				if ($dataAction == 'buy') 
					$limitOrder = $this->buyLimit ($market, $QT, $rate);
				else if ($dataAction == 'sell')
					$limitOrder = $this->sellLimit ($market, $QT, $rate);
		
				//var_dump($limitOrder);
				$orderId = $limitOrder->uuid; 
		
				$amt = $amt - $QT; //substract cost from balance 
				$cost = $QT * $rate;  //how much this order costs USDT

				$output .= 'action: '. $dataAction.' |  bid: '.$bid.' | QT: '.$QT.' | cost: '.$cost.' | amt: '.$amt.' | orderId: '.$orderId.' <br />';
			} //if($amt > 0)
			else { //balance limit reached - exit loop
				$output .= ' end loop'; 
				break;
			}
		} //foreach($getOrderBook as $orderBook) 
		//echo $output;

		return $output;
	}

	/**
	 * Place a buy order in a specific market. 
	 * Market orders are disabled on API
	 */
	public function buyMarket ($market, $quantity) {
		$params = array (
			'market'   => $market,
			'quantity' => $quantity
		);
		return $this->call ('market/buymarket', $params, true);
	}

	/**
	 * Place a sell order in a specific market. 
	 * Market orders are disabled on API
	 */
	public function sellMarket ($market, $quantity) {
		$params = array (
			'market'   => $market,
			'quantity' => $quantity
		);
		return $this->call ('market/sellmarket', $params, true);
	}

	/**
	 * Cancel a buy or sell order 
	 * @param string $uuid id of sell or buy order
	 * @return array
	 */
	public function cancel ($uuid) {
		$params = array ('uuid' => $uuid);
		return $this->call ('market/cancel', $params, true);
	}

	/**
	 * Get all orders that you currently have opened. A specific market can be requested
	 * @param string $market  literal for the market (ex: BTC-LTC)
	 * @return array
	 */
	public function getOpenOrders ($market = null) {
		$params = array ('market' => $market);
		return $this->call ('market/getopenorders', $params, true);
	}

	/**
	 * Retrieve all balances from your account
	 * @return array
	 */
	public function getBalances () {
		return $this->call ('account/getbalances', array(), true);
	}

	/**
	 * Retrieve the balance from your account for a specific currency
	 * @param string $currency literal for the currency (ex: LTC)
	 * @return array
	 */
	public function getBalance ($currency)
	{
		$params = array ('currency' => $currency);
		return $this->call ('account/getbalance', $params, true);
	}

	/**
	 * Retrieve or generate an address for a specific currency. If one 
	 * does not exist, the call will fail and return ADDRESS_GENERATING 
	 * until one is available.
	 * @param string $currency literal for the currency (ex: LTC)
	 * @return array
	 */
	public function getDepositAddress ($currency) {
		$params = array ('currency' => $currency);
		return $this->call ('account/getdepositaddress', $params, true);
	}


	/**
	 * Retrieve a single order by uuid
	 * @param string $uuid 	the uuid of the buy or sell order
	 * @return array
	 */
	public function getOrder ($uuid) {
		$params = array ('uuid' => $uuid);
		return $this->call ('account/getorder', $params, true);
	}

	/**
	 * Retrieve your order history
	 * @param string $market  (optional) a string literal for the market (ie. BTC-LTC). If ommited, will return for all markets
	 * @param integer $count  (optional) the number of records to return
	 * @return array
	 */
	public function getOrderHistory ($market = null, $count = null) {
		$params = array ();

		if ($market) {
			$params['market'] = $market;
		}

		if ($count) {
			$params['count'] = $count;
		}

		return $this->call ('account/getorderhistory', $params, true);
	}

	/**
	 * Retrieve your withdrawal history
	 * @param string $currency  (optional) a string literal for the currecy (ie. BTC). If omitted, will return for all currencies
	 * @param integer $count    (optional) the number of records to return
	 * @return array
	 */
	public function getWithdrawalHistory ($currency = null, $count = null) {
		$params = array ();

		if ($currency) {
			$params['currency'] = $currency;
		}

		if ($count) {
			$params['count'] = $count;
		}

		return $this->call ('account/getwithdrawalhistory', $params, true);
	}

	/**
	 * Retrieve your deposit history
	 * @param string $currency  (optional) a string literal for the currecy (ie. BTC). If omitted, will return for all currencies
	 * @param integer $count    (optional) the number of records to return
	 * @return array
	 */
	public function getDepositHistory ($currency = null, $count = null) {
		$params = array ();

		if ($currency) {
			$params['currency'] = $currency;
		}

		if ($count) {
			$params['count'] = $count;
		}

		return $this->call ('account/getdeposithistory', $params, true);
	}
}