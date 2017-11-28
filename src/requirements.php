;<?php
// simple checks for base requirements to environment
if (extension_loaded('curl') && function_exists('curl_version')) {
    $curl = true;
    echo "cURL extension for PHP - Ok\n";
} else {
    $curl = false;
    echo "cURL extension for PHP - NOT FOUND!\n";
}
if (ini_get('allow_url_fopen')) {
    $urlOpen = true;
    echo "PHP settings allow_url_fopen - Ok\n";
} else {
    $urlOpen = false;
    echo "PHP settings allow_url_fopen - NOT ALLOWED!\n";
}
if (extension_loaded('openssl')) {
    $openssl = true;
    echo "OpenSSL extension for PHP - Ok\n";
} else {
    $openssl = false;
    echo "OpenSSL extension for PHP - NOT FOUND!\n";
}
if (extension_loaded('simplexml')) {
    $simplexml = true;
    echo "SimpleXML extension for PHP - Ok\n";
} else {
    $simplexml = false;
    echo "SimpleXML extension for PHP - NOT FOUND!\n";
}
if (extension_loaded('phar')) {
    $phar = true;
    echo "Phar extension for PHP - Ok\n";
} else {
    $phar = false;
    echo "Phar extension for PHP - NOT FOUND!\n";
}