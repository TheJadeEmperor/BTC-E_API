<?php

class Gate {
  private $key;
  private $secret;
  private $host = 'https://api.gateio.ws'; //production

  public function __construct($key, $secret) {
    $this->key    = $key;
    $this->secret = $secret;
  }

  public function signature($method = 'GET', $request_path = '', $query_string = '', $body = '', $timestamp = false) {
    $secret = $this->secret;

    $body = is_array($body) ? json_encode($body) : $body; // Body must be in json format
    $timestamp = $timestamp ? $timestamp : time();
    $hashedPayload = hash("sha512", ($body !== null) ? $body : "");

    $fmt = "%s\n%s\n%s\n%s\n%s";

    $signatureString = sprintf(
      $fmt,
      $method,
      $request_path,
      $query_string,
      $hashedPayload,
      $timestamp
    );

    return hash_hmac("sha512", $signatureString, $secret);
  }

  public function getBalances() {
    $host = $this->host;
    $key = $this->key;

    $method = 'GET';
    $request_path = '/api/v4/spot/accounts';
    $query_string = '';
    $body = '';
    $timestamp = time();

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
        "KEY:".$key,
        "TIMESTAMP:".$timestamp,
        "SIGN:".$this->signature($method, $request_path, $query_string, $body, $timestamp)
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
 
    curl_close($curl);

    if ($err) {
      echo "cURL Error #:" . $err;
    }

    $responseArr = json_decode($response,true);
    $getBalances = array();
    
    $counter = 0;
    foreach($responseArr as $index) {
      $currency = $index['currency'];
      $available = $index['available'];
      $locked = $index['locked'];
     
      if ($available > 0.001 || $locked > 0.001) {
        $getBalances[$counter++] = array(
            'currency' => $currency,
            'available' => $available,
            'locked' => $locked
        );
        $counter++;
      }
    }

    //echo '<pre>';
    //echo '<h1>Get Spot Balance: </h1><br>';
    //print_r($getBalances);
    return $getBalances;
  }

  public function getSubBalances() {
    $host = $this->host;
    $key = $this->key;

    $method = 'GET';
    $request_path = '/api/v4/wallet/sub_account_balances';
    $query_string = '';
    $body = '';
    $timestamp = time();

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
        "KEY:".$key,
        "TIMESTAMP:".$timestamp,
        "SIGN:".$this->signature($method, $request_path, $query_string, $body, $timestamp)
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
 
    curl_close($curl);

    if ($err) {
      echo "cURL Error #:" . $err;
    }

    $responseArr = json_decode($response,true);

    //echo '<pre>';
    //echo '<h1>Get Balances: </h1><br>';
    //print_r($responseArr);
    return $responseArr;
  }

  public function getMarketPrice($currencyPair)
  {
    $host = $this->host;
    $key = $this->key;

    $method = 'GET';
    $request_path = '/api/v4/spot/tickers?currency_pair='.$currencyPair;

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
        "Content-Type:application/json"
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    //echo curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);

    if ($err) {
      echo "cURL Error #:" . $err;
    }

    $responseArr = json_decode($response,true);

    // echo '<pre><h1>Get Market Prices: </h1><br>';
    // print_r($responseArr);
    return $responseArr;
  }

  public function buyOrder($type, $pair, $amount, $price)
  {
    $host = $this->host;
    $key = $this->key;

    $method = 'POST';
    $request_path = '/api/v4/spot/orders';
    $query_string = '';
    $body ='{"account":"spot","side":"buy","currency_pair":"'.$pair.'","type":"'.$type.'","amount":"'.$amount.'","price":"'.$price.'"}';
    $timestamp = time();

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
        "KEY:".$key,
        "TIMESTAMP:".$timestamp,
        "SIGN:".$this->signature($method, $request_path, $query_string, $body, $timestamp)
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    //echo curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);

    if ($err) {
      echo "cURL Error #:" . $err;
    }

    $responseArr = json_decode($response,true);

    echo '<pre>';
    echo '<h1>Buy Order: </h1><br>';
    print_r($responseArr);
    return $responseArr;
  }

  public function sellOrder($type, $pair, $amount, $price)
  {
    $host = $this->host;
    $key = $this->key;

    $method = 'POST';
    $request_path = '/api/v4/spot/orders';
    $query_string = '';
    $body ='{"account":"spot","side":"sell","currency_pair":"'.$pair.'","type":"'.$type.'","amount":"'.$amount.'","price":"'.$price.'"}';
    $timestamp = time();

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
        "KEY:".$key,
        "TIMESTAMP:".$timestamp,
        "SIGN:".$this->signature($method, $request_path, $query_string, $body, $timestamp)
      ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    //echo curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);

    if ($err) {
      echo "cURL Error #:" . $err;
    }

    $responseArr = json_decode($response,true);

    echo '<pre>';
    echo '<h1>Sell Order: </h1><br>';
    print_r($responseArr);
    return $responseArr;
  } //public function sellOrder
} //class Gate


?>