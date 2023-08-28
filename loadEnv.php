<?php

$env_file_path = realpath(__DIR__ . "/.env");
//Check .envenvironment file exists
if (!is_file($env_file_path)) {
    throw new ErrorException("Environment File is Missing.");
}
//Check .envenvironment file is readable
if (!is_readable($env_file_path)) {
    throw new ErrorException("Permission Denied for reading the " . ($env_file_path) . ".");
}
//Check .envenvironment file is writable
if (!is_writable($env_file_path)) {
    throw new ErrorException("Permission Denied for writing on the " . ($env_file_path) . ".");
}

function loadEnv()
{
    $env_file_path = realpath(__DIR__ . "/.env");

    $var_arrs = array();
    // Open the .en file using the reading mode
    $fopen = fopen($env_file_path, 'r');
    if ($fopen) {
        //Loop the lines of the file
        while (($line = fgets($fopen)) !== false) {
            // Check if line is a comment
            $line_is_comment = (substr(trim($line), 0, 1) == '#') ? true : false;
            // If line is a comment or empty, then skip
            if ($line_is_comment || empty(trim($line)))
                continue;

            // Split the line variable and succeeding comment on line if exists
            $line_no_comment = explode("#", $line, 2)[0];
            // Split the variable name and value
            $env_ex              = preg_split('/(\s?)\=(\s?)/', $line_no_comment);
            $env_name            = trim($env_ex[0]);
            $env_value           = isset($env_ex[1]) ? trim($env_ex[1]) : "";
            $var_arrs[$env_name] = $env_value;
        }
        // Close the file
        fclose($fopen);
    }

    foreach ($var_arrs as $name => $value) {
        putenv("{$name}={$value}");
    }
}

loadEnv();
// write to .env file

function putEnvKeyValue($key, $value)
{
    $env_file_path = realpath(__DIR__ . "/.env");
    // Check .envenvironment file is writable
    if (!is_writable($env_file_path)) {
        throw new ErrorException("Permission Denied for writing on the " . ($env_file_path) . ".");
    }
    // Open the .env file using the reading mode
    $fopen = fopen($env_file_path, 'r');
    if ($fopen) {
        $updated = false;
        $lines   = [];
        //Loop the lines of the file
        while (($line = fgets($fopen)) !== false) {
            // Check if line is a comment or empty
            if (substr(trim($line), 0, 1) == '#' || empty(trim($line))) {
                $lines[] = $line;
                continue;
            }
            // Split the line variable and succeeding comment on line if exists
            $line_no_comment = explode("#", $line, 2)[0];
            // Split the variable name and value
            $env_ex    = preg_split('/(\s?)\=(\s?)/', $line_no_comment);
            $env_name  = trim($env_ex[0]);
            $env_value = isset($env_ex[1]) ? trim($env_ex[1]) : "";
            // Check if key exists
            if ($env_name === $key) {
                $lines[] = "{$key}={$value}\n";
                $updated = true;
            } else {
                $lines[] = $line;
            }
        }
        // Close the file
        fclose($fopen);
        // If key does not exist, append it to the end of the file
        if (!$updated) {
            $lines[] = "{$key}={$value}\n";
        }
        // Open the .env file using the writing mode
        $fopen = fopen($env_file_path, 'w');
        if ($fopen) {
            // Write the updated lines to the file
            fwrite($fopen, implode("", $lines));
            // Close the file
            fclose($fopen);
        } else {
            throw new ErrorException("Failed to open the " . ($env_file_path) . " file.");
        }
    } else {
        throw new ErrorException("Failed to open the " . ($env_file_path) . " file.");
    }
    loadEnv();
}