<?php
// API Credentials
$username = 'CapitalPartnersAcademy';
$password = 'QrQQ2SDF$$';

// Create curl request to get access token
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "https://demo.tradovateapi.com/v1/session");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, 
            "{\"name\":\"$username\",\"password\":\"$password\"}"); 
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);   

// Execute request
$result = curl_exec($ch);
echo $result."\n";
// Get access token from response
$token = json_decode($result, true)['accessToken'];

// Use $token to authorize websocket...

// Close curl  
curl_close($ch);