<?php

include_once __DIR__ . '/vendor/autoload.php';
include_once "templates/base.php";

echo pageHeader('Search Console');

/*************************************************
 * Ensure you've downloaded your oauth credentials
 ************************************************/
if (!$oauth_credentials = getOAuthCredentialsFile()) {
    echo missingOAuth2CredentialsWarning();
    return;
}

/************************************************
Make an API request on behalf of a user. In
this case we need to have a valid OAuth 2.0
token for the user, so we need to send them
through a login flow. To do this we need some
information from our API console project.
 ************************************************/
/************************************************
 * NOTICE:
 * The redirect URI is to this page, e.g:
 * http://localhost:8080/simplefileupload.php
 ************************************************/
$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

$client = new Google_Client();
$client->setAuthConfig($oauth_credentials);
$client->setRedirectUri($redirect_uri);
$client->addScope("https://www.googleapis.com/auth/webmasters");

/************************************************
 * When we create the service here, we pass the
 * client to it. The client then queries the service
 * for the required scopes, and uses that when
 * generating the authentication URL later.
 ************************************************/
$service = new Google_Service_Webmasters($client);

/************************************************
 * If we're logging out we just need to clear our
 * local access token in this case
 ************************************************/
if (isset($_REQUEST['logout'])) {
    unset($_SESSION['access_token']);
}

/************************************************
 * If we have a code back from the OAuth 2.0 flow,
 * we need to exchange that with the
 * Google_Client::fetchAccessTokenWithAuthCode()
 * function. We store the resultant access token
 * bundle in the session, and redirect to ourself.
 ************************************************/
if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);

    // store in the session also
    $_SESSION['access_token'] = $token;
    header("Location: " . "load_data.php");
    exit();
}

/************************************************
If we have an access token, we can make
requests, else we generate an authentication URL.
 ************************************************/
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
    $client->setAccessToken($_SESSION['access_token']);
} else {
    $authUrl = $client->createAuthUrl();
}
?>
<div class="box">
    <div class="request">
        <a class='login' href='<?= $authUrl ?>'>Connect Me!</a>
    </div>
</div>
