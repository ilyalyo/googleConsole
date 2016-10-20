<?php
echo 'loading data...';

if (!(isset($_SESSION['access_token']) && $_SESSION['access_token'])) {
    header("Location: " . "index.php");
    exit();
}

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