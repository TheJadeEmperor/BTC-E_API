<?php
class Binance {
  private $key;
  private $secret;
  private $BASE_URL = 'https://api.binance.us/';

  public function __construct($key, $secret) {
		$this->key    = $key;
		$this->secret = $secret;
	}

  public function signature($query_string, $secret) {
    return hash_hmac('sha256', $query_string, $secret);
  }

  public function sendRequest($method, $path) {
    $KEY = $this->key;
    $BASE_URL = $this->BASE_URL;
    
    $url = "${BASE_URL}${path}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-MBX-APIKEY:'.$KEY));    
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, $method == "POST" ? true : false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
    $response = curl_getinfo($ch);
      
    // if you wish to print the response headers
    // echo print_r($response);

    curl_close ($ch);
    return json_decode($execResult, true);
  }

  public function signedRequest($method, $path, $parameters = []) {
    $SECRET = $this->secret;

    $parameters['timestamp'] = round(microtime(true) * 1000);
    $query = $this->buildQuery($parameters);
    $signature = $this->signature($query, $SECRET);
    return $this->sendRequest($method, "${path}?${query}&signature=${signature}");
  }

  public function buildQuery(array $params) {
    $query_array = array();
    foreach ($params as $key => $value) {
        if (is_array($value)) {
            $query_array = array_merge($query_array, array_map(function ($v) use ($key) {
                return urlencode($key) . '=' . urlencode($v);
            }, $value));
        } else {
            $query_array[] = urlencode($key) . '=' . urlencode($value);
        }
    }
    return implode('&', $query_array);
  }

  public function getMarketPrice($symbol) {
    // get orderbook
    $responseArr = $this->sendRequest('GET', 'api/v3/depth?symbol='.$symbol.'&limit=5');
    
    // echo '<pre>';
    // echo '<h1>Get Market Price: </h1><br>';
    return $responseArr;
  }

  public function getAccount() {
    // get account information, make sure API key and secret are set
    $responseArr = $this->signedRequest('GET', 'api/v3/account');
    $balances = $responseArr['balances'];
    $balancesArr = [];

    foreach ($balances as $key => $value) {
      if($value['free'] > 0 || $value['locked'] > 0)
      {
        $balancesArr[] = $value;
      }
    }

    $responseArr = array();
    $responseArr['balances'] = $balancesArr;
    
    echo '<pre>';
    echo '<h1>Get Account: </h1><br>';
    $responseArr['function'] = '--getAccount';
    print_r($responseArr);
    return $responseArr;
  }

  public function buyOrder($type, $pair, $amount, $price) {
    // place order, make sure API key and secret are set
    $responseArr = $this->signedRequest('POST', 'api/v3/order', [
      'symbol' => $pair,
      'side' => 'BUY',
      'type' => $type,
      'timeInForce' => 'GTC',
      'quantity' => $amount,
      'price' => $price,
      'newOrderRespType' => 'FULL' //optional
    ]);

    if($type == 'MARKET' or $type == 'market') {
      $responseArr = $this->signedRequest('POST', 'api/v3/order', [
        'symbol' => $pair,
        'side' => 'BUY',
        'type' => $type,
        'quantity' => $amount,
        'newOrderRespType' => 'FULL' //optional
      ]);
    }
    
    $responseArr['function'] = 'buyOrder';
    echo '<pre>';
    echo '<h1>Buy Order: </h1><br>';
    print_r($responseArr);
    return $responseArr;
  }

  public function sellOrder($type, $pair, $amount, $price) {
    // place order, make sure API key and secret are set
    $responseArr = $this->signedRequest('POST', 'api/v3/order', [
      'symbol' => $pair,
      'side' => 'SELL',
      'type' => $type,
      'timeInForce' => 'GTC',
      'quantity' => $amount,
      'price' => $price,
      'newOrderRespType' => 'FULL' //optional
    ]);
    

    if($type == 'MARKET' or $type == 'market') {
      $responseArr = $this->signedRequest('POST', 'api/v3/order', [
        'symbol' => $pair,
        'side' => 'BUY',
        'type' => $type,
        'quantity' => $amount,
        'newOrderRespType' => 'FULL' //optional
      ]);
    }

    $responseArr['function'] = 'sellOrder';
    echo '<pre>';
    echo '<h1>Sell Order: </h1><br>';
    print_r($responseArr);
    return $responseArr;
  }

  public function deleteOrder($symbol, $order_id) {
    // delete order
    $responseArr = $this->signedRequest('DELETE', 'api/v3/order', [
      'symbol' => $symbol,
      'orderId' => $order_id
    ]);
    
    echo '<pre>';
    echo '<h1>Delete Order: </h1><br>';
    print_r($responseArr);
    return $responseArr;
  }

  public function deleteOrders($symbol) {
    // delete all open orders
    $responseArr = $this->signedRequest('DELETE', 'api/v3/openOrders', [
      'symbol' => $symbol
    ]);
    
    echo '<pre>';
    echo '<h1>Delete Orders: </h1><br>';
    print_r($responseArr);  
    return $responseArr;
  }
}

// Put API key and secret values in the below function

// $binance = new Binance($key, $secret);
// $response = $binance->getAccount();


// $getMarketPrice = getMarketPrice('VTHOUSDT');

// $bid = $getMarketPrice[bids][0][0];
// $ask = $getMarketPrice[asks][0][0];

// buyOrder('LIMIT', 'VTHOUSDT', 600, $ask);
// sellOrder('LIMIT', 'VTHOUSDT', 600, $bid);
//  deleteOrder('VTHOUSDT', 1);
// deleteOrders('VTHOUSDT');

// echo $bid.' '.$ask;