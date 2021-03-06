<?php
/*
 * Copyright 2011 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

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

    // redirect back to the example
    header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
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

/************************************************
If we're signed in and have a request to shorten
a URL, then we create a new URL object, set the
unshortened URL, and call the 'insert' method on
the 'url' resource. Note that we re-store the
access_token bundle, just in case anything
changed during the request - the main thing that
might happen here is the access token itself is
refreshed if the application has offline access.
 ************************************************/
$websites = [];
$startDate = "";
$endDate = "";
$data = null;
if(isset($_GET['daterange'])) {
    $arrDate = explode(' - ', $_GET['daterange']);
    if (count($arrDate) > 1) {
        $startDate = $arrDate[0];
        $endDate = $arrDate[1];
    }
}

if ($client->getAccessToken()) {

    foreach ($service->sites->listSites()->getSiteEntry() as $siteEntry)
        $websites [] = $siteEntry['siteUrl'];

    if(isset($_GET['website'])){
        $searchRequest = new Google_Service_Webmasters_SearchAnalyticsQueryRequest();

        if(!empty($startDate) && !empty($endDate)) {
            $searchRequest->setStartDate($startDate);
            $searchRequest->setEndDate($endDate);
        }

        if(isset($_GET['searchType']))
            $searchRequest->setSearchType($_GET['searchType']);

        try {
            $searchRequest->setDimensions(["date"]);
            $data = $service->searchanalytics->query($_GET['website'], $searchRequest);
        }
        catch (Exception $e){
            echo $e->getMessage();
        }
        $_SESSION['access_token'] = $client->getAccessToken();
    }
}
?>

<?php if (isset($authUrl)): ?>
    <div class="box">
        <div class="request">
            <a class='login' href='<?= $authUrl ?>'>Connect Me!</a>
        </div>
    </div>
<?php else : ?>
    <a class='logout' href='?logout'>Logout</a>

    <form id="url" method="GET" action="<?= $_SERVER['PHP_SELF'] ?>">
        <div class="form-group row">
            <div class="col-md-2">
                <label for="website">Websites</label>
                <select name="website" id="website" class="selectpicker form-control">
                    <?php
                    foreach ($websites as $url)
                        echo "<option value='$url'>$url</option>";
                    ?>
                </select>
            </div>
            <script type="text/javascript">
                $('#website').val(<?php echo '[\''.implode($_GET['website'], '\', \'').'\']' ;?>);
            </script>

            <div class="col-md-2">
                <label for="page">Page</label>
                <select name="page[]" id="page" class="selectpicker form-control" multiple>
                </select>
            </div>
            <script type="text/javascript">
                $('#page').val(<?php echo '[\''.implode($_GET['page'], '\', \'').'\']' ;?>);
            </script>

            <div class="col-md-1">
                <label for="searchType">SearchType</label>
                <select name="searchType" id="searchType" class="selectpicker form-control" >
                    <option value='web'>web</option>
                    <option value='image'>image</option>
                    <option value='video'>video</option>
                </select>
            </div>
            <script type="text/javascript">
                $('#searchType').val("<?php echo $_GET['searchType'];?>");
            </script>

            <div class="col-md-2">
                <label for="device">Device</label>
                <select name="device[]" id="device" class="selectpicker form-control" multiple>
                    <option value='desktop'>desktop</option>
                    <option value='mobile'>mobile</option>
                    <option value='tablet'>tablet</option>
                </select>
            </div>
            <script type="text/javascript">
                $('#device').val(<?php echo '[\''.implode($_GET['device'], '\', \'').'\']' ;?>);
            </script>

            <div class="col-md-1">
                <label for="country">Country</label>
                <select name="country[]" id="country" class="selectpicker form-control" multiple>
                </select>
            </div>
            <script type="text/javascript">
                $('#country').val(<?php echo '[\''.implode($_GET['country'], '\', \'').'\']' ;?>);
            </script>

            <div class="col-md-1">
                <label for="alltime">All time</label>
                <input type="checkbox" name="alltime" id="alltime" class=""/>
            </div>
            <div class="col-md-3">
                <label for="daterange">Date Range</label>
                <div class="input-group">
                    <input type="text" name="daterange" id="daterange" class="form-control"/>
                      <span class="input-group-btn">
                          <input type="submit" value="submit" class="btn">
                      </span>
                </div>
            </div>
        </div>
    </form>

    <script>
        $(function() {
            var dateNow = new Date();
            var threeMonth = new Date();
            threeMonth.setMonth(dateNow.getMonth()-3);
            var twoMonth = new Date();
            twoMonth.setMonth(dateNow.getMonth()-2);
            var oneMonth = new Date();
            oneMonth.setMonth(dateNow.getMonth()-1);

            $('#daterange').daterangepicker({
                "ranges": {
                    "Last 30 Days": [
                        oneMonth,
                        dateNow
                    ],
                    "Last 60 Days": [
                        twoMonth,
                        dateNow
                    ],
                    "Last 90 Days": [
                        threeMonth,
                        dateNow
                    ]
                },
                locale: {
                    "format" : "YYYY-MM-DD"
                },
                "showCustomRangeLabel": false,
                "alwaysShowCalendars": true,
                <?php if (!empty($startDate) && !empty($endDate)): ?>
                "startDate": "<?php echo $startDate; ?>",
                "endDate": "<?php echo $endDate; ?>",
                <?php endif;?>
                "opens": "left"
            }, function(start, end, label) {
                console.log("New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')");
            });
        });
    </script>

    <?php if(!empty($data)): ?>
        <script type="text/javascript">

            // Load the Visualization API and the corechart package.
            google.charts.load('current', {'packages':['line', 'corechart']});

            // Set a callback to run when the Google Visualization API is loaded.
            google.charts.setOnLoadCallback(drawChart);

            function drawChart() {
                // Create the data table.
                var data = new google.visualization.DataTable();
                data.addColumn('date', 'Date');
                data.addColumn('number', "Clicks");
                data.addColumn('number', "Impressions");
                data.addColumn('number', "ctr");
                data.addColumn('number', "Position");
                data.addRows([
                    <?php
                        foreach ($data->getRows() as $row)
echo "[ new Date('{$row->keys[0]}'), $row->clicks, $row->impressions, $row->ctr, $row->position],";
                        ?>
                ]);

                var materialOptions = {
                    width: 900,
                    height: 400,
                    axes: {
                        // Adds labels to each axis; they don't have to match the axis names.
                        y: {
                                Temps: {label: 'Temps (Celsius)'}
                           }
                        }
                    };

                // Instantiate and draw our chart, passing in some options.
                var chartDiv = document.getElementById('chart_div');
                var materialChart = new google.charts.Line(chartDiv);
                materialChart.draw(data, materialOptions);
            }

        </script>

        <body>
        <!--Div that will hold the pie chart-->
        <div id="chart_div"></div>
        </body>
    <?php var_dump($data) ?>
    <?php else: ?>
    <?php echo "Nothing to show"?>
    <?php endif; ?>
<?php endif ?>

