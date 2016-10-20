<?php
session_start();
if(!isset($_SESSION['access_token']) || !isset($_SESSION['client'])) {
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
