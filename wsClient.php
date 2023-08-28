<?php
// namespace Beshoy\TradovateWsCleint;
require 'vendor/autoload.php';
require_once 'helpers.php';
require_once 'loadEnv.php';
require_once 'accessToken.php';
use \WebSocket\Client;

// Get variables from .env file


// Use access token

$token =getAccessToken();
if ($token === false) {
  logger( "Failed to get access token");
  die;
}
$client = new Client("wss://demo-d.tradovateapi.com/v1/websocket");
// $client->text('Hello PieSocket!');
 
// Generate access token 
// $token = '5dF1Mo4TNs_3uaOWD74Fd4ABgqJeH3gZ_rlFqm3brHl9s_ayHr3rH5IrzSww7ifQKUbBCdy6wGtTPkmSsi7BiB9S_uiSIWBuYFq1ejLLZ7A1Ehme23RBKGgbt_r8jlkG7rJCiamNzyruXVqMpCNeHjPYtX_xHu6dak4wZdWyMXQtaj4qlX8SeGCIYZyfQ_q2txOFuS0UVA7PGg'; 

// Authorize request
$request = "authorize\n0\n\n$token";

// Send authorize request
$client->send($request); 


// Wait for authorize response
$client->send("user/syncrequest");
while($client->isConnected()) {
  echo "connected\n";
  // listen for user/syncrequest
  $response = $client->receive();
  // $response = ($response;
  // logger( 'connected ' . $response . "\n");
  // if (file_put_contents('response.txt', $response, FILE_APPEND) === false) {
  //   logger( "Failed to write to file");
  // }
  // keep alive @see https://api.tradovate.com/#section/Server-Frames
  if ($response !== null && $response == 'h') {
    $client->send(json_encode([]));
  }

  if ($response !== null && strpos($response,'"s":200') !== false) {
    // $response = $client->receive(); 
  $response = substr($response, 1);
    
  $unserializedResponse = json_decode($response);
  echo 'type '.gettype($unserializedResponse);
    logger( '======= response ====== '. "\n");
    logger( $unserializedResponse );
    logger( '======= end of response ====== '. "\n");
    
    file_put_contents('response.txt', print_r($response,true),FILE_APPEND);
    // break;
  }
}

?>
