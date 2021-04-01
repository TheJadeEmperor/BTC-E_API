<?php
class Binance {
  private $key;
  private $secret; 
  private $BASE_URL;

  public function __construct ($apiKey, $apiSecret) {
		$this->key    = $apiKey;
		$this->secret = $apiSecret;
		$this->baseUrl = 'https://api.binance.us/';
	}

}

$KEY = $binance_api_key;
$SECRET = $binance_api_secret;
$BASE_URL = 'https://api.binance.us/';

function signature($query_string, $secret) {
    return hash_hmac('sha256', $query_string, $secret);
}

function sendRequest($method, $path) {
  global $KEY;
  global $BASE_URL;
  
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

function signedRequest($method, $path, $parameters = []) {
  global $SECRET;

  $parameters['timestamp'] = round(microtime(true) * 1000);
  $query = buildQuery($parameters);
  $signature = signature($query, $SECRET);
  return sendRequest($method, "${path}?${query}&signature=${signature}");
}

function buildQuery(array $params) {
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

function getMarketPrice($symbol) {
  // get orderbook
  $responseArr = sendRequest('GET', 'api/v3/depth?symbol='.$symbol.'&limit=5');
  
  echo '<pre>';
  echo '<h1>Get Market Price: </h1><br>';
  // print_r($responseArr);
  return $responseArr;
}

function getAccount() {
  // get account information, make sure API key and secret are set
  $responseArr = signedRequest('GET', 'api/v3/account');
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
  $responseArr['function'] = 'getAccount';
  print_r($responseArr);
  return $responseArr;
}

function buyOrder($type, $pair, $amount, $price) {
  // place order, make sure API key and secret are set
  $responseArr = signedRequest('POST', 'api/v3/order', [
    'symbol' => $pair,
    'side' => 'BUY',
    'type' => $type,
    'timeInForce' => 'GTC',
    'quantity' => $amount,
    'price' => $price,
    'newOrderRespType' => 'FULL' //optional
  ]);

  if($type == 'MARKET' or $type == 'market') {
    $responseArr = signedRequest('POST', 'api/v3/order', [
      'symbol' => $pair,
      'side' => 'BUY',
      'type' => $type,
      'quantity' => $amount,
      'newOrderRespType' => 'FULL' //optional
    ]);
  }
  
  echo '<pre>';
  echo '<h1>Buy Order: </h1><br>';
  print_r($responseArr);
  return $responseArr;
}

function sellOrder($type, $pair, $amount, $price) {
  // place order, make sure API key and secret are set
  $responseArr = signedRequest('POST', 'api/v3/order', [
    'symbol' => $pair,
    'side' => 'SELL',
    'type' => $type,
    'timeInForce' => 'GTC',
    'quantity' => $amount,
    'price' => $price,
    'newOrderRespType' => 'FULL' //optional
  ]);
  

  if($type == 'MARKET' or $type == 'market') {
    $responseArr = signedRequest('POST', 'api/v3/order', [
      'symbol' => $pair,
      'side' => 'BUY',
      'type' => $type,
      'quantity' => $amount,
      'newOrderRespType' => 'FULL' //optional
    ]);
  }

  echo '<pre>';
  echo '<h1>Sell Order: </h1><br>';
  print_r($responseArr);
  return $responseArr;
}

function deleteOrder($symbol, $order_id) {
  // delete order
  $responseArr = signedRequest('DELETE', 'api/v3/order', [
    'symbol' => $symbol,
    'orderId' => $order_id
  ]);
  
  echo '<pre>';
  echo '<h1>Delete Order: </h1><br>';
  print_r($responseArr);
  return $responseArr;
}

function deleteOrders($symbol) {
  // delete all open orders
  $responseArr = signedRequest('DELETE', 'api/v3/openOrders', [
    'symbol' => $symbol
  ]);
  
  echo '<pre>';
  echo '<h1>Delete Orders: </h1><br>';
  print_r($responseArr);  
  return $responseArr;
}


// $getAccount = getAccount();

// $getMarketPrice = getMarketPrice('VTHOUSDT');

// $bid = $getMarketPrice[bids][0][0];
// $ask = $getMarketPrice[asks][0][0];

// buyOrder('LIMIT', 'VTHOUSDT', 600, $ask);
// sellOrder('LIMIT', 'VTHOUSDT', 600, $bid);
//  deleteOrder('VTHOUSDT', 1);
// deleteOrders('VTHOUSDT');

// echo $bid.' '.$ask;