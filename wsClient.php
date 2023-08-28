<?php
// namespace Beshoy\TradovateWsCleint;
require 'vendor/autoload.php';
require_once 'loadEnv.php';
use \WebSocket\Client;

// Get variables from .env file
$DEMO_BASEURL = getenv('DEMO_BASEURL');
$VENDOR_NAME = getenv('VENDOR_NAME');
$PASSWORD = getenv('PASSWORD');
$APP_ID = getenv('APP_ID');
$APP_VERSION = getenv('APP_VERSION');
$SEC = getenv('SEC');
$CID = getenv('CID');
$DEVICE_ID = getenv('DEVICE_ID');
$LIVE_BASEURL = getenv('LIVE_BASEURL');

// Prepare credentials
$credentials = array(
    'name' => $VENDOR_NAME,
    'password' => $PASSWORD,
    'appId' => $APP_ID,
    'appVersion' => $APP_VERSION,
    'cid' => $CID,
    'sec' => $SEC
);

// Prepare HTTP request
$options = array(
    'http' => array(
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($credentials),
    ),
);
$context  = stream_context_create($options);
$result = file_get_contents($DEMO_BASEURL . '/auth/accesstokenrequest', false, $context);
if ($result === FALSE) { /* Handle error */ }

// Parse response
$response = json_decode($result);
$accessToken = $response->accessToken;
$mdAccessToken = $response->mdAccessToken;
$userId = $response->userId;

// Use access token
$token = $accessToken;

$client = new Client("wss://demo-d.tradovateapi.com/v1/websocket");
// $client->text('Hello PieSocket!');
 
// Generate access token 
// $token = '5dF1Mo4TNs_3uaOWD74Fd4ABgqJeH3gZ_rlFqm3brHl9s_ayHr3rH5IrzSww7ifQKUbBCdy6wGtTPkmSsi7BiB9S_uiSIWBuYFq1ejLLZ7A1Ehme23RBKGgbt_r8jlkG7rJCiamNzyruXVqMpCNeHjPYtX_xHu6dak4wZdWyMXQtaj4qlX8SeGCIYZyfQ_q2txOFuS0UVA7PGg'; 

// Authorize request
$request = "authorize\n0\n\n$token";

// Send authorize request
$client->send($request); 


// Wait for authorize response
while($client->isConnected()) {
  $response = $client->receive();
  echo 'connected ' . $response . "\n";
  if (file_put_contents('response.txt', $response) === false) {
    echo "Failed to write to file";
  }
  // listen for user/syncrequest
  $client->send("user/syncrequest");
  // keep alive @see https://api.tradovate.com/#section/Server-Frames
  if ($response !== null && $response == 'h') {
    $client->send(json_encode([]));
  }

  if ($response !== null && strpos($response,'"s":200') !== false) {
    $response = $client->receive(); 
    $unserializedResponse = json_decode($response);
    file_put_contents('response.txt', $unserializedResponse);
    break;
  }
}

?>
