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
$dateFormat = 'Y-m-d';

foreach ($service->sites->listSites()->getSiteEntry() as $siteEntry)
    $websites [] = $siteEntry['siteUrl'];

$db = new Db();

foreach ($websites as $website){
    $site_id = $db->is_client_website_exist($client_id, $website);

    if($site_id == null)
        $site_id = $db->add_website($client_id, $website);

    $startDate = $db->get_last_record_date($site_id);

    if($startDate == null){
        $startDate = new DateTime();
        $startDate->modify('-3 month');
    }
    else
        $startDate = new DateTime($startDate);

    $endDate = new DateTime();
    $endDate->modify('-1 day');

    $interval = date_diff($startDate, $endDate);
    $daysBetween = $interval->format('%a');

    //don't need to update data
    if($daysBetween == 0)
        continue;

    $tmpSDate = clone $startDate;
    $tmpSDate->modify('+1 day');
    $tmpEDate = clone $startDate;

    while ($tmpSDate <= $endDate)
    {
        $tmpEDate->modify('+7 day');

        if($tmpEDate > $endDate)
            $tmpEDate = $endDate;

//        makeRequest($tmpSDate, $tmpEDate, $site_id, $website);

        $tmpSDate->modify('+7 day');
    }
}

function makeRequest($startDate, $endDate, $site_id, $website){
    global $service;
    global $db;

    $searchRequest = new Google_Service_Webmasters_SearchAnalyticsQueryRequest();

    $searchRequest->setStartDate($startDate);
    $searchRequest->setEndDate($endDate);

    try {
        $searchRequest->setRowLimit(5000);
        $searchRequest->setDimensions(["date", "country", "device", "query", "page"]);
        $data = $service->searchanalytics->query($website, $searchRequest);
        var_dump($data);
        foreach ($data->getRows() as $row){
            $db->add_record($site_id, $row->keys[0], $row->keys[1], $row->keys[2], $row->keys[3], $row->keys[4],
                $row->clicks, $row->impressions, $row->ctr, $row->position);
        }

    }
    catch (Exception $e){
        echo $e->getMessage();
    }
}