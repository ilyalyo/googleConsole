<?php

/* Ad hoc functions to make the examples marginally prettier.*/
function isWebRequest()
{
  return isset($_SERVER['HTTP_USER_AGENT']);
}

function pageHeader($title)
{
  $ret = "<!doctype html>
  <html>
  <head>
    <title>" . $title . "</title>
    <link href='styles/style.css' rel='stylesheet' type='text/css' />
    
    
    <!-- GRID -->
    <link href=\"styles/bootstrap.min.css\" rel=\"stylesheet\">
    <link href=\"styles/font-awesome.min.css\" rel=\"stylesheet\">

    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src=\"/js/ie8-responsive-file-warning.js\"></script><![endif]-->
    <script src=\"js/jquery.js\"></script>
    <script src=\"js/jquery.ui.js\"></script>

    <link href=\"styles/mesour.grid.css\" rel=\"stylesheet\">
    <script src=\"js/mesour.grid.js\"></script>
    
    <!-- Include Required Prerequisites -->
    <script type=\"text/javascript\" src=\"//cdn.jsdelivr.net/momentjs/latest/moment.min.js\"></script>

    <script type=\"text/javascript\" src=\"//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js\"></script>
    <link rel =\"stylesheet\" type=\"text/css\" href=\"//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css\"/>

    <!-- Google charts -->
    <script type=\"text/javascript\" src=\"https://www.gstatic.com/charts/loader.js\"></script>
    
    <!-- Multioption select-->
    <link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.11.2/css/bootstrap-select.min.css\">
    <script src=\"https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.11.2/js/bootstrap-select.min.js\"></script >

  </head>
  <body>\n";

  $ret .= "<header><h1>" . $title . "</h1></header>";

 // Start the session (for storing access tokens and things)
  if (!headers_sent()) {
    session_start();
  }

  return $ret;
}


function pageFooter()
{
  $ret = "    
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src=\"js/jquery.cookie.js\"></script>
    <script src=\"js/jquery.nette.js\"></script>
    <script src=\"js/netteForms.js\"></script>
    <script src=\"js/jquery.niceCodeLines.js\"></script>
    <!-- Latest compiled and minified JavaScript -->
    <script src=\"js/bootstrap.min.js\"></script>
    <script src=\"js/main.js\"></script>
    </html>";

  return $ret;
}

function missingApiKeyWarning()
{
  $ret = "
    <h3 class='warn'>
      Warning: You need to set a Simple API Access key from the
      <a href='http://developers.google.com/console'>Google API console</a>
    </h3>";

  return $ret;
}

function missingClientSecretsWarning()
{
  $ret = "
    <h3 class='warn'>
      Warning: You need to set Client ID, Client Secret and Redirect URI from the
      <a href='http://developers.google.com/console'>Google API console</a>
    </h3>";

  return $ret;
}

function missingServiceAccountDetailsWarning()
{
  $ret = "
    <h3 class='warn'>
      Warning: You need download your Service Account Credentials JSON from the
      <a href='http://developers.google.com/console'>Google API console</a>.
    </h3>
    <p>
      Once downloaded, move them into the root directory of this repository and
      rename them 'service-account-credentials.json'.
    </p>
    <p>
      In your application, you should set the GOOGLE_APPLICATION_CREDENTIALS environment variable
      as the path to this file, but in the context of this example we will do this for you.
    </p>";

  return $ret;
}

function missingOAuth2CredentialsWarning()
{
  $ret = "
    <h3 class='warn'>
      Warning: You need to set the location of your OAuth2 Client Credentials from the
      <a href='http://developers.google.com/console'>Google API console</a>.
    </h3>
    <p>
      Once downloaded, move them into the root directory of this repository and
      rename them 'oauth-credentials.json'.
    </p>";

  return $ret;
}

function checkServiceAccountCredentialsFile()
{
  // service account creds
  $application_creds = __DIR__ . '/../service-account-credentials.json';

  return file_exists($application_creds) ? $application_creds : false;
}

function getOAuthCredentialsFile()
{
  // oauth2 creds
  $oauth_creds = __DIR__ . '/../oauth-credentials.json';

  if (file_exists($oauth_creds)) {
    return $oauth_creds;
  }

  return false;
}

function setClientCredentialsFile($apiKey)
{
  $file = __DIR__ . '/../tests/.apiKey';
  file_put_contents($file, $apiKey);
}


function getApiKey()
{
  $file = __DIR__ . '/../tests/.apiKey';
  if (file_exists($file)) {
    return file_get_contents($file);
  }
}

function setApiKey($apiKey)
{
  $file = __DIR__ . '/../tests/.apiKey';
  file_put_contents($file, $apiKey);
}
