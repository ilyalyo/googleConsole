<?php
include_once __DIR__ . '/vendor/autoload.php';
include_once "templates/base.php";

echo pageHeader('Saving Data..');

if(!isset($_SESSION['access_token'])) {
    session_unset();
    header("location: index.php");
}

var_dump($_SESSION['access_token']);

$client = new Google_Client();
$client->setAuthConfig($oauth_credentials);
$client->setRedirectUri($redirect_uri);
$client->addScope("https://www.googleapis.com/auth/webmasters");
$client->setAccessToken($_SESSION['access_token']);
$service = new Google_Service_Webmasters($client);
var_dump($service->sites->listSites()->getSiteEntry());

$searchRequest = new Google_Service_Webmasters_SearchAnalyticsQueryRequest();

$startDate = date('Y-m-d');
$endDate = date('Y-m-d', strtotime("-1 month"));

$searchRequest->setStartDate($startDate);
$searchRequest->setEndDate($endDate);

try {
    $searchRequest->setDimensions(["date", "country", "device", "query", "page"]);
    $data = $service->searchanalytics->query($_GET['website'], $searchRequest);
    var_dump($data);
}
catch (Exception $e){
    echo $e->getMessage();
}