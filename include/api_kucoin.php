<?php

class Kucoin {
  private $host = 'https://api.kucoin.com'; //production
  //private $host = 'https://openapi-sandbox.kucoin.com'; //sandbox
  private $apiVersion;
  private $key;
  private $secret; 
  private $passphrase;

  public function __construct($key, $secret, $passphrase) {
    $this->apiVersion = 'v1';
    $this->key = $key;
    $this->secret = $secret;
    $this->passphrase = $passphrase;
	}

  public function signature($request_path = '', $body = '', $timestamp = false, $method = 'GET') {
    $secret = $this->secret;
  
    $body = is_array($body) ? json_encode($body) : $body; // Body must be in json format
    $timestamp = $timestamp ? $timestamp : time() * 1000;
    
    $what = $timestamp . $method . $request_path . $body;
    
    return base64_encode(hash_hmac("sha256", $what, $secret, true));
  }

  public function checkBalance() {
    $host = $this->host;
    $key = $this->key;
    $passphrase = $this->passphrase;

    $method = 'GET';
    $request_path = '/api/'.$this->apiVersion.'/accounts';
    $timestamp = time() * 1000;
    $body = '';
  
    $curl = curl_init();
  
    curl_setopt_array($curl, array(
      CURLOPT_URL => $host . $request_path,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_HTTPHEADER => array(
        "Content-Type:application/json",
        "KC-API-SIGN:".$this->signature($request_path, $body, $timestamp, $method),
        "KC-API-TIMESTAMP:".$timestamp,
        "KC-API-KEY:".$key,
        "KC-API-PASSPHRASE:".$passphrase
      ),
    ));
  
    $response = curl_exec($curl);
    $responseArr = json_decode($response,true);
  
    curl_close($curl);
  
    $responseArr['function'] = 'checkBalance()';
    //echo '<pre>'; print_r($responseArr); echo '</pre>';
    return $responseArr;
  }

  public function getMarketPrice($currencyPair) {
    $host = $this->host;
    $key = $this->key;
    $passphrase = $this->passphrase;
  
    $method = 'GET';
    $request_path = '/api/v1/market/orderbook/level1?symbol='.$currencyPair;
    $timestamp = time() * 1000;
    $body = '';
  
    $curl = curl_init();
  
    curl_setopt_array($curl, array(
      CURLOPT_URL => $host . $request_path,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_HTTPHEADER => array(
        "Content-Type:application/json",
        "KC-API-SIGN:".$this->signature($request_path, $body, $timestamp, $method),
        "KC-API-TIMESTAMP:".$timestamp,
        "KC-API-KEY:".$key,
        "KC-API-PASSPHRASE:".$passphrase
      ),
    ));
  
    $response = curl_exec($curl);
    $responseArr = json_decode($response,true);
  
    curl_close($curl);
  
    $responseArr['function'] = '-getMarketPrice($currencyPair)';
    $responseArr['currencyPair'] = $currencyPair;
    // echo '<pre>'; print_r($responseArr); echo '</pre>';
    return $responseArr;
  }
  
  //type is market or limit
  public function buyOrder($type, $pair, $buyQT, $ask) {
    $host = $this->host;
    $key = $this->key;
    $passphrase = $this->passphrase;
  
    $method = 'POST';
    $request_path = '/api/v1/orders';
    $timestamp = time() * 1000;
    $body ='{"side":"buy","symbol":"'.$pair.'","type":"'.$type.'","price":"'.$ask.'","size":"'.$buyQT.'","clientOid":"'.microtime(true).'"}';
  
    $curl = curl_init();
  
    curl_setopt_array($curl, array(
      CURLOPT_URL => $host . $request_path,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_POST => 1,
      CURLOPT_POSTFIELDS => $body,
      CURLOPT_HTTPHEADER => array(
        "Content-Type:application/json",
        "KC-API-SIGN:".$this->signature($request_path, $body, $timestamp, $method),
        "KC-API-TIMESTAMP:".$timestamp,
        "KC-API-KEY:".$key,
        "KC-API-PASSPHRASE:".$passphrase
      ),
    ));
  
    $response = curl_exec($curl);
    $responseArr = json_decode($response,true);
  
    curl_close($curl);
  
    $responseArr['function'] = 'function buyOrder($type, $pair, $buyQT, $ask)';
    echo '<pre>'; print_r($responseArr); echo '</pre>';
    return $responseArr;
  }
  
  public function sellOrder($type, $pair, $sellQT, $ask) {
    $host = $this->host;
    $key = $this->key;
    $passphrase = $this->passphrase;
  
    $method = 'POST';
    $request_path = '/api/v1/orders';
    $timestamp = time() * 1000;
    $body ='{"side":"sell","symbol":"'.$pair.'","type":"'.$type.'","price":"'.$ask.'","size":"'.$sellQT.'","clientOid":"'.microtime(true).'"}';
  
    $curl = curl_init();
  
    curl_setopt_array($curl, array(
      CURLOPT_URL => $host . $request_path,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_POST => 1,
      CURLOPT_POSTFIELDS => $body,
      CURLOPT_HTTPHEADER => array(
        "Content-Type:application/json",
        "KC-API-SIGN:".$this->signature($request_path, $body, $timestamp, $method),
        "KC-API-TIMESTAMP:".$timestamp,
        "KC-API-KEY:".$key,
        "KC-API-PASSPHRASE:".$passphrase
      ),
    ));
  
    $response = curl_exec($curl);
    $responseArr = json_decode($response,true);
  
    curl_close($curl);
  
    $responseArr['function'] = 'function sellOrder($type, $pair, $sellQT, $ask)';
    echo '<pre>'; print_r($responseArr); echo '</pre>';
    return $responseArr;
  } 
  
  /**
  Array (
      [code] => 200000
      [data] => Array
          (
              [cancelledOrderIds] => Array
                  (
                      [0] => 602370228313f700068bc252
                  )
  
          )
  
  )
  */
  public function cancelOrder($orderId) {
    $host = $this->host;
    $key = $this->key;
    $passphrase = $this->passphrase;
  
    $method = 'DELETE';
    $request_path = '/api/v1/orders/'.$orderId;
    $timestamp = time() * 1000;
    $body = '';
  
    $curl = curl_init();
  
    curl_setopt_array($curl, array(
      CURLOPT_URL => $host . $request_path,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_HTTPHEADER => array(
        "Content-Type:application/json",
        "KC-API-SIGN:".$this->signature($request_path, $body, $timestamp, $method),
        "KC-API-TIMESTAMP:".$timestamp,
        "KC-API-KEY:".$key,
        "KC-API-PASSPHRASE:".$passphrase
      ),
    ));
  
    $response = curl_exec($curl);
    $responseArr = json_decode($response,true);
  
    curl_close($curl);
  
    $responseArr['function'] = 'cancelOrder($orderId)';
    echo '<pre>'; print_r($responseArr); echo '</pre>';
    return $responseArr;
  }
} //class Kucoin


$host = 'https://api.kucoin.com'; //production


function signature($request_path = '', $body = '', $timestamp = false, $method = 'GET') {
  global $secret;

  $body = is_array($body) ? json_encode($body) : $body; // Body must be in json format
  $timestamp = $timestamp ? $timestamp : time() * 1000;
  $what = $timestamp . $method . $request_path . $body;
  return base64_encode(hash_hmac("sha256", $what, $secret, true));
}


function checkBalance() {
  global $host;
  global $key;
  global $passphrase;

  $method = 'GET';
  $request_path = '/api/v1/accounts';
  $timestamp = time() * 1000;
  $body = '';

  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => $host . $request_path,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => $method,
    CURLOPT_HTTPHEADER => array(
      "Content-Type:application/json",
      "KC-API-SIGN:".signature($request_path, $body, $timestamp, $method),
      "KC-API-TIMESTAMP:".$timestamp,
      "KC-API-KEY:".$key,
      "KC-API-PASSPHRASE:".$passphrase
    ),
  ));

  $response = curl_exec($curl);
  $responseArr = json_decode($response,true);

  curl_close($curl);

  $responseArr['function'] = 'checkBalance( )';
  echo '<pre>'; print_r($responseArr); echo '</pre>';
  return $responseArr;
}

//type is market or limit
function buyOrder($type, $pair, $buyQT, $ask) {
  global $host;
  global $key;
  global $passphrase;

  $method = 'POST';
  $request_path = '/api/v1/orders';
  $timestamp = time() * 1000;
  $body ='{"side":"buy","symbol":"'.$pair.'","type":"'.$type.'","price":"'.$ask.'","size":"'.$buyQT.'","clientOid":"'.microtime(true).'"}';

  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => $host . $request_path,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => $method,
    CURLOPT_POST => 1,
    CURLOPT_POSTFIELDS => $body,
    CURLOPT_HTTPHEADER => array(
      "Content-Type:application/json",
      "KC-API-SIGN:".signature($request_path, $body, $timestamp, $method),
      "KC-API-TIMESTAMP:".$timestamp,
      "KC-API-KEY:".$key,
      "KC-API-PASSPHRASE:".$passphrase
    ),
  ));

  $response = curl_exec($curl);
  $responseArr = json_decode($response,true);

  curl_close($curl);

  $responseArr['function'] = 'function buyOrder($type, $pair, $buyQT, $ask)';
  echo '<pre>'; print_r($responseArr); echo '</pre>';
  return $responseArr;
}

function sellOrder($type, $pair, $sellQT, $ask) {
  global $host;
  global $key;
  global $passphrase;

  $method = 'POST';
  $request_path = '/api/v1/orders';
  $timestamp = time() * 1000;
  $body ='{"side":"sell","symbol":"'.$pair.'","type":"'.$type.'","price":"'.$ask.'","size":"'.$sellQT.'","clientOid":"'.microtime(true).'"}';

  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => $host . $request_path,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => $method,
    CURLOPT_POST => 1,
    CURLOPT_POSTFIELDS => $body,
    CURLOPT_HTTPHEADER => array(
      "Content-Type:application/json",
      "KC-API-SIGN:".signature($request_path, $body, $timestamp, $method),
      "KC-API-TIMESTAMP:".$timestamp,
      "KC-API-KEY:".$key,
      "KC-API-PASSPHRASE:".$passphrase
    ),
  ));

  $response = curl_exec($curl);
  $responseArr = json_decode($response,true);

  curl_close($curl);

  $responseArr['function'] = 'function sellOrder($type, $pair, $sellQT, $ask)';
  echo '<pre>'; print_r($responseArr); echo '</pre>';
  return $responseArr;
}

/**
Array (
    [code] => 200000
    [data] => Array
        (
            [cancelledOrderIds] => Array
                (
                    [0] => 602370228313f700068bc252
                )

        )

)
*/
function cancelOrder($orderId) {
  global $host;
  global $key;
  global $passphrase;

  $method = 'DELETE';
  $request_path = '/api/v1/orders/'.$orderId;
  $timestamp = time() * 1000;
  $body = '';

  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => $host . $request_path,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => $method,
    CURLOPT_HTTPHEADER => array(
      "Content-Type:application/json",
      "KC-API-SIGN:".signature($request_path, $body, $timestamp, $method),
      "KC-API-TIMESTAMP:".$timestamp,
      "KC-API-KEY:".$key,
      "KC-API-PASSPHRASE:".$passphrase
    ),
  ));

  $response = curl_exec($curl);
  $responseArr = json_decode($response,true);

  curl_close($curl);

  $responseArr['function'] = 'cancelOrder($orderId)';
  echo '<pre>'; print_r($responseArr); echo '</pre>';
  return $responseArr;
}

function getMarketPrice($currencyPair) {
  global $host;
  global $key;
  global $passphrase;

  $method = 'GET';
  $request_path = '/api/v1/market/orderbook/level1?symbol='.$currencyPair;
  $timestamp = time() * 1000;
  $body = '';

  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => $host . $request_path,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => $method,
    CURLOPT_HTTPHEADER => array(
      "Content-Type:application/json",
      "KC-API-SIGN:".signature($request_path, $body, $timestamp, $method),
      "KC-API-TIMESTAMP:".$timestamp,
      "KC-API-KEY:".$key,
      "KC-API-PASSPHRASE:".$passphrase
    ),
  ));

  $response = curl_exec($curl);
  $responseArr = json_decode($response,true);

  curl_close($curl);

  $responseArr['function'] = 'getMarketPrice($currencyPair)';
  $responseArr['currencyPair'] = $currencyPair;
  echo '<pre>'; print_r($responseArr); echo '</pre>';
  return $responseArr;
}

?>