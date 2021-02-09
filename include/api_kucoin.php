<?php
  //include('include/config.php');
  $host = 'https://api.kucoin.com'; //production

  //$host = 'https://openapi-sandbox.kucoin.com'; //sandbox

  function signature($request_path = '', $body = '', $timestamp = false, $method = 'GET') 
  {
    global $secret;

    $body = is_array($body) ? json_encode($body) : $body; // Body must be in json format
    $timestamp = $timestamp ? $timestamp : time() * 1000;
    $what = $timestamp . $method . $request_path . $body;
    return base64_encode(hash_hmac("sha256", $what, $secret, true));
  }

  function checkBalance()
  {
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

    echo '<pre>';
    echo '<h1>Check Balance: </h1><br>';
    print_r($responseArr);
  }

  function MakeOrder()
  {
    global $host;
    global $key;
    global $passphrase;

    $method = 'POST';
    $request_path = '/api/v1/orders';
    $timestamp = time() * 1000;
    $body ='{"side":"sell","symbol":"XRP-USDT","type":"limit","price":"0.0445","size":"1","clientOid":"'.microtime(true).'"}';

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

    echo '<pre>';
    echo '<h1>Place a New Order: </h1><br>';
    print_r($responseArr);
  }

  function CancelOrder()
  {
    global $host;
    global $key;
    global $passphrase;

    $method = 'DELETE';
    $orderId = "5c714d17cdaba40702ea1abd";
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

    echo '<pre>';
    echo '<h1>Cancel Order: </h1><br>';
    print_r($responseArr);
  }

?>