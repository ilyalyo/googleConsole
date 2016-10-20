<?php
@session_start();
if(!isset($_SESSION['access_token']) && !isset($_SESSION['client']))
    header("location: index.php");

/************************************************
 * If we're logging out we just need to clear our
 * local access token in this case
 ************************************************/
if (isset($_REQUEST['logout'])) {
    unset($_SESSION['access_token']);
    unset($_SESSION['client']);
    header("Location: " . "index.php");
}
