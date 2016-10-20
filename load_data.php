<?php
include_once __DIR__ . '/vendor/autoload.php';
include_once "templates/base.php";
include_once "db.php";

echo pageHeader('Saving Data..');

if(!isset($_SESSION['access_token'])) {
    session_unset();
    header("location: index.php");
}

var_dump($_SESSION['access_token']);
var_dump($_SESSION['client_id']);

$client = new Google_Client();
$client->setAuthConfig($oauth_credentials);
$client->setRedirectUri($redirect_uri);
$client->addScope("https://www.googleapis.com/auth/webmasters");
$client->setAccessToken($_SESSION['access_token']);
$client_id = $_SESSION['client_id'];

$service = new Google_Service_Webmasters($client);

$websites = [];
foreach ($service->sites->listSites()->getSiteEntry() as $siteEntry)
    $websites [] = $siteEntry['siteUrl'];

$db = new Db();

foreach ($websites as $website){
    $site_id = $db->is_client_website_exist($client_id, $website);

    if($site_id == false)
        $site_id = $db->add_website($client_id, $website);

    $startDate = $db->get_last_record_date($site_id);

    if($startDate == null)
        $startDate = date('Y-m-d', strtotime("-3 month"));

    $endDate = date('Y-m-d', strtotime("-1 day"));

    $searchRequest = new Google_Service_Webmasters_SearchAnalyticsQueryRequest();

    $searchRequest->setStartDate($startDate);
    $searchRequest->setEndDate($endDate);

    try {
        $searchRequest->setRowLimit(5);
        $searchRequest->setDimensions(["date", "country", "device", "query", "page"]);
        $data = $service->searchanalytics->query($website, $searchRequest);
        var_dump($data);
        foreach ($data->getRows() as $row){
            $db->add_record($site_id, $row->keys[0], $row->keys[1], $row->keys[2], $row->keys[3],
                $row->clicks, $row->impressions, $row->ctr, $row->position);
        }

    }
    catch (Exception $e){
        echo $e->getMessage();
    }
    die();
}
