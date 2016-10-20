<?php
include_once("restd.php");
echo pageHeader('Search Console');

echo 'loading data...';
var_dump($_SESSION['access_token']);

$client = new Google_Client();
$client->setAuthConfig($oauth_credentials);
$client->setRedirectUri($redirect_uri);
$client->addScope("https://www.googleapis.com/auth/webmasters");
$client->setAccessToken($_SESSION['access_token']);
$service = new Google_Service_Webmasters($client);
var_dump($service->sites->listSites()->getSiteEntry());


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