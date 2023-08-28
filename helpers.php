<?php 

if(!function_exists('logger')) {
 function logger($message) {
    $logFile = 'log.txt';
    $log = fopen($logFile, 'a');
    if (is_array($message)) {
         fwrite($log, print_r($message, true) . "\n");
    } else {
        fwrite($log, $message . "\n");
    }
    
   //  fwrite($log, $message . "\n");
    fclose($log);
   //  error_log($message,3,$logFile);
 }
}