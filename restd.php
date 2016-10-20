<?php
include_once __DIR__ . '/vendor/autoload.php';
include_once "templates/base.php";

if(!isset($_SESSION['access_token'])) {
    session_unset();
    header("location: index.php");
}

/************************************************
 * If we're logging out we just need to clear our
 * local access token in this case
 ************************************************/
if (isset($_REQUEST['logout'])) {
    session_unset();
    header("Location: " . "index.php");
}
