<?php

function getAccessToken()
{
    if (tokenIsValid()) {
        return getenv('STORAGE_KEY');
    } else {
        return requestAccessToken();
    }
}

function tokenIsValid()
{
    $expiry = getenv('EXPIRATION_KEY');
    if ($expiry === '') {
        return false;
    } else {
        $expiry = strtotime($expiry);
        $now    = time();
        if ($expiry > $now) {
            logger('Token is valid');
            return true;
        } else {
            return false;
        }
    }
}

function setAccessToken($accessToken, $expiry)
{
    logger('Setting access token');
    putEnvKeyValue("STORAGE_KEY",$accessToken);
    putEnvKeyValue("EXPIRATION_KEY",$expiry);
    return true;
}
function requestAccessToken()
{
    logger('Requesting access token');
    checkPenalty();
    $DEMO_BASEURL = getenv('DEMO_BASEURL');
    $VENDOR_NAME  = getenv('VENDOR_NAME');
    $PASSWORD     = getenv('PASSWORD');
    $APP_ID       = getenv('APP_ID');
    $APP_VERSION  = getenv('APP_VERSION');
    $SEC          = getenv('SEC');
    $CID          = getenv('CID');

    // Prepare credentials
    $credentials = array(
        'name'       => $VENDOR_NAME,
        'password'   => $PASSWORD,
        'appId'      => $APP_ID,
        'appVersion' => $APP_VERSION,
        'cid'        => $CID,
        'sec'        => $SEC
    );

    // Prepare HTTP request
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($credentials),
        ),
    );
    $context = stream_context_create($options);
    $result  = file_get_contents($DEMO_BASEURL . '/auth/accesstokenrequest', false, $context);
    if ($result === FALSE) {
        echo "Failed to get access token";
        logger( "Failed to get access token");
        exit;
    }
    // logger($result);
    $response = json_decode($result);
    // if blocked by rate limiter, handle retry
    if (isset($response->p_ticket)){
        logger('Rate limiter triggered. Retrying operation.');
        return handleRetry($response);}
    setAccessToken($response->accessToken, $response->expirationTime);
    return $response->accessToken;
}   


function handleRetry($json)
{
    $ticket  = $json['p-ticket'];
    $time    = $json['p-time'];
    $captcha = $json['p-captcha'];

    if ($captcha) {
        logger('Captcha present, cannot retry auth request via third party application. Please try again in an hour.');
        return;
    }

    logger("Time Penalty present. Retrying operation in {$time}s");
    $penalty = strtotime('now') + $time;
    putEnvKeyValue('PENALTY_KEY', $penalty);
    logger("Time penalty expires at {$time}");
    
    sleep($time);
    requestAccessToken();
}

function checkPenalty()
{
    $penalty = getenv('PENALTY_KEY');
    if ($penalty === '') {
        return;
    } else {
        $penalty = strtotime($penalty);
        $now     = strtotime('now');
        if ($penalty > $now) {
            logger('Time penalty present. Waiting until penalty expires.');
            sleep($penalty - $now);
            return;
        } else {
            return;
        }
    }
}