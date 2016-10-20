<?php
include_once("restd.php");
echo pageHeader('Search Console');

echo 'loading data...';
var_dump($_SESSION['access_token']);
var_dump($_SESSION['client']);
echo 22;

/** @var Google_Client $client */
$client = $_SESSION['client'];

/************************************************
 * When we create the service here, we pass the
 * client to it. The client then queries the service
 * for the required scopes, and uses that when
 * generating the authentication URL later.
 ************************************************/
$service = new Google_Service_Webmasters($client);


sleep(1000);
header("Location: " . "main.php");
exit();