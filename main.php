<?php
include_once __DIR__ . '/vendor/autoload.php';
include_once "templates/base.php";
include_once "db.php";

echo pageHeader('Search Console');

if(!isset($_SESSION['access_token']) || isset($_REQUEST['logout'])) {
    session_unset();
    header("location: index.php");
}

$client = new Google_Client();
$client->setAuthConfig($oauth_credentials);
$client->setRedirectUri($redirect_uri);
$client->addScope("https://www.googleapis.com/auth/webmasters");
$client->setAccessToken($_SESSION['access_token']);
$client_id = $_SESSION['client_id'];

$service = new Google_Service_Webmasters($client);

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
}
?>
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
