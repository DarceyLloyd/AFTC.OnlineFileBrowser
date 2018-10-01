<?php


ini_set('display_errors', 1); // Show all errors
error_reporting(E_ALL);
ini_set("error_log", "./log.txt");



// Password to be encrypted for a .htpasswd file
$clearTextPassword = 'SomePassword';

// Encrypt password
$password = crypt($clearTextPassword, base64_encode($clearTextPassword));

// Print encrypted password
echo(__DIR__);
echo("<hr>");
echo $password;
?>