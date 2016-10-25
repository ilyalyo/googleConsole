<?php

require_once __DIR__ . '/vendor/autoload.php';
include_once __DIR__ . '/db.php';

define('LOG_DIR', __DIR__ . '/../log');
define('TEMP_DIR', __DIR__ . '/../temp/cache');

// Start the session (for storing access tokens and things)
if (!headers_sent()) {
    session_start();
}

if(!isset($_SESSION['access_token']) || isset($_REQUEST['logout'])) {
    session_unset();
    header("location: index.php");
}
//$client_id = "115417360953986887127";
$client_id = $_SESSION['client_id'];

$db = new Db();

$websites = [];
$startDate = "";
$endDate = "";
$data = [];
$data_graph = [];

if(isset($_GET['daterange'])) {
    $_SESSION['daterange'] = $_GET['daterange'];
    $arrDate = explode(' - ', $_GET['daterange']);
    if (count($arrDate) > 1) {
        $startDate = $arrDate[0];
        $endDate = $arrDate[1];
    }
}
else if(isset($_SESSION['daterange'])){
    $arrDate = explode(' - ', $_SESSION['daterange']);
    if (count($arrDate) > 1) {
        $startDate = $arrDate[0];
        $endDate = $arrDate[1];
    }
}
else {
    $endDate = date('Y-m-d');
    $startDate = date('Y-m-d', strtotime("-1 month"));
}

$websites = $db->get_websites($client_id);

$website_id = null;
if(isset($_GET['website'])){
    $website_id = $_GET['website'];
    $_SESSION['website'] = $website_id;
}
else if(isset($_SESSION['website']))
    $website_id = $_SESSION['website'];
else if(!isset($websites) && count($websites) > 0)
    $website_id = $websites[0]['id'];
if(isset($website_id)) {
    $countries = $db->get_countries($website_id);
    $pages = $db->get_pages($website_id);
    $sql_graph = "SELECT `date`, SUM(`clicks`) as `clicks`,  SUM(`impressions`) as `impressions`,  
AVG(NULLIF(`ctr` ,0)) as `ctr`,  AVG (`position`) as `position`  FROM `data` WHERE `site_id` = $website_id AND";
    $sql = "SELECT * FROM `data` WHERE `site_id` = $website_id AND";

    $q = "";
    if (!empty($startDate) && !empty($endDate))
        $q .= " STR_TO_DATE(`date`, '%Y-%m-%d') 
            BETWEEN STR_TO_DATE('$startDate', '%Y-%m-%d') AND STR_TO_DATE('$endDate', '%Y-%m-%d')";
    else
        $q .= " 1=1";

    $q .= arrToSql('device');
    $q .= arrToSql('country');
    $q .= arrToSql('page');

    $sql_graph .= $q . ' GROUP BY `date`';
    $sql_graph .= ' ORDER BY STR_TO_DATE(`date`, \'%Y-%m-%d\')';
    $sql .= $q . ' ORDER BY STR_TO_DATE(`date`, \'%Y-%m-%d\')';

    $data = $db->runSql($sql);
    $data_graph = $db->runSqlGraph($sql_graph);
}

function arrToSql($param){
    $sql = "";
    if(!empty($_GET[$param])) {
        $sql .= " AND $param IN(";
        $sql .= '\'' . implode('\',\'', $_GET[$param]) . '\'';
        $sql .= ")";
    }
    return $sql;
}
?>
<!DOCTYPE html>
<html lang="en" class=" is-copy-enabled is-u2f-enabled">
<head>
    <link href='styles/style.css' rel='stylesheet' type='text/css' />

    <!-- Multioption select-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.11.2/css/bootstrap-select.min.css">

    <link rel ="stylesheet" type="text/css" href="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css"/>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="styles/grid/bootstrap.min.css">
    <link rel="stylesheet" href="styles/grid/bootstrap-datetimepicker.min.css">
    <link rel="stylesheet" href="styles/grid/font-awesome.min.css">
    <link rel="stylesheet" href="styles/grid/mesour.grid.min.css">
    <!-- Google charts -->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

</head>
<body>

<?php
$application = new \Mesour\UI\Application('uss2q');
$application->setRequest($_REQUEST);
$application->run();

$source = new Mesour\DataGrid\Sources\ArrayGridSource('uss2q','id', $data);
$grid = new Mesour\UI\DataGrid('uss2q', $application);

$grid->setSource($source);
$grid->enableFilter(FALSE);
$grid->enablePager(10);
if(!empty($data)) {
    $grid->setDefaultOrder('date', 'DESC');

    $grid->addText('date', 'Date');
    $grid->addText('query', 'query');
    $grid->addText('page', 'Page');
    $grid->addText('country', 'country');
    $grid->addText('device', 'device');

    $grid->addNumber('clicks', 'Clicks');
    $grid->addNumber('impressions', 'impressions');
    $grid->addNumber('ctr', 'ctr');
    $grid->addNumber('position', 'position');
}
$createdGrid = $grid->create();
?>
<a class='logout' href='?logout'>Logout</a>

<form id="url" method="GET" action="<?= $_SERVER['PHP_SELF'] ?>">
    <div class="container">
        <div class="form-group row">
            <div class="col-md-2">
                <label for="website">Websites</label>
                <select name="website" id="website" class="selectpicker form-control">
                    <?php
                    foreach ($websites as $site)
                        echo "<option value='{$site['id']}'>{$site['site_url']}</option>";
                    ?>
                </select>
            </div>
            <script type="text/javascript">
            </script>

            <div class="col-md-2">
                <label for="page">Page</label>
                <select name="page[]" id="page" class="selectpicker form-control" multiple>
                    <?php
                    foreach ($pages as $page)
                        echo "<option value='{$page['page']}'>{$page['page']} - {$page['clicks']}</option>";
                    ?>
                </select>
            </div>

            <div class="col-md-1">
                <label for="searchType">SearchType</label>
                <select name="searchType" id="searchType" class="selectpicker form-control" disabled>
                    <option value='web'>web</option>
                    <option value='image'>image</option>
                    <option value='video'>video</option>
                </select>
            </div>
            <script type="text/javascript">
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
            </script>

            <div class="col-md-2">
                <label for="country">Country</label>
                <select name="country[]" id="country" class="selectpicker form-control" multiple>
                    <?php
                    foreach ($countries as $country)
                        echo "<option value='{$country['country']}'>{$country['country']} - {$country['clicks']}</option>";
                    ?>
                </select>
            </div>
            <script type="text/javascript">
            </script>

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
    </div>
</form>
<div class="container">
    <div class="row">
        <?php if(!empty($data)): ?>
            <div id="chart_div"></div>
        <?php else :?>
            <h1>No data</h1>
        <?php endif;?>
        <div class="row">
            <div class="container" style="margin-top: 50px;">
                <?php
                    echo $createdGrid->render();
                ?>
            </div>
        </div>
    </div>
</div>
<!-- Latest compiled and minified JavaScript -->
<script src="js/grid/jquery.js"></script>
<script src="js/grid/jquery.ui.js"></script>

<script src="js/grid/bootstrap.min.js"></script>
<script src="js/grid/moment.min.js"></script>

<script type="text/javascript" src="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.11.2/js/bootstrap-select.min.js"></script>

<script type="text/javascript">
    <?php if(isset($_GET['website'])):?>
        $('#website').val("<?php echo $_GET['website'];?>");
    <?php endif;?>
    <?php if(isset($_GET['page'])):?>
        $('#page').val(<?php echo '[\''.implode($_GET['page'], '\', \'').'\']' ;?>);
    <?php endif;?>
    <?php if(isset($_GET['searchType'])):?>
        $('#searchType').val("<?php echo $_GET['searchType'];?>")
    <?php endif;?>
    <?php if(isset($_GET['device'])):?>
        $('#device').val(<?php echo '[\''.implode($_GET['device'], '\', \'').'\']' ;?>);
    <?php endif;?>
    <?php if(isset($_GET['country'])):?>
        $('#country').val(<?php echo '[\''.implode($_GET['country'], '\', \'').'\']' ;?>);
    <?php endif;?>
</script>

<script src="js/grid/mesour.grid.min.js"></script>
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
            foreach ($data_graph as $row)
                echo "[ new Date('{$row['date']}'), {$row['clicks']}, {$row['impressions']}, {$row['ctr']}, {$row['position']}],";
            ?>
        ]);

        var materialOptions = {
            width: 900,
            height: 400,
            series: {
                // Gives each series an axis name that matches the Y-axis below.
                0: {axis: 'Temps'},
                1: {axis: 'Daylight'}
            },
            axes: {
                // Adds labels to each axis; they don't have to match the axis names.
            }
        };

        // Instantiate and draw our chart, passing in some options.
        var chartDiv = document.getElementById('chart_div');
        var materialChart = new google.charts.Line(chartDiv);
        materialChart.draw(data, materialOptions);
    }
</script>
</body>
</html>