<?php
class Db
{
    const DB_USER = 'root';
    const DB_PASS = 'root';
    const DB_NAME = 'googleConsole';
    private $connection;
    
    public function __construct()
    {
        $this->connect();
    }
    
    private function connect()
    {
        $this->connection = mysqli_connect('localhost', $this::DB_USER, $this::DB_PASS, $this::DB_NAME);
        $this->connection ->set_charset("utf8");
        if (mysqli_connect_errno()) {
            echo "Failed to connect to MySQL";
        }
    }

    public function is_client_website_exist($client_id, $website){
        $sql = "SELECT `id` FROM clients WHERE `client_id` = '$client_id' AND site_url = '$website'";
        return mysqli_query($this->connection, $sql)->fetch_object()->id;
    }

    public function add_website($client_id, $website)
    {
        $sql = "INSERT INTO clients (`client_id`,`site_url`) VALUES ('$client_id','$website')";
        if (!mysqli_query($this->connection, $sql))
            die(mysqli_error($this->connection));
        return $this->connection->insert_id;
    }

    public function get_last_record_date($site_id){
        $sql = "SELECT MAX(`date`) as `date`  FROM `data` WHERE `site_id` = $site_id GROUP BY STR_TO_DATE(`date`, '%Y%m%d')";
        return mysqli_query($this->connection, $sql)->fetch_object()->date;
    }

    public function add_record($site_id, $date, $country, $device, $query, $page, $clicks, 
                               $impressions, $ctr, $position){
        $safe_query = mysqli_real_escape_string($this->connection, $query);
        $safe_page = mysqli_real_escape_string($this->connection, $page);
        
        $sql = "INSERT INTO `data` (`site_id`,`date`, `country`, `device`, `query`, `page`, 
`clicks`, `impressions`, `ctr`, `position`) 
        VALUES ($site_id,'$date', '$country', '$device', '$safe_query', '$safe_page', 
        $clicks, $impressions, $ctr, $position)";
        if (!mysqli_query($this->connection, $sql))
            die(mysqli_error($this->connection));
    }

    public function get_websites($client_id){
        $sql = "SELECT `id`, `site_url`FROM `clients` WHERE `client_id` = $client_id";
        $query = mysqli_query($this->connection, $sql);
        $result = [];
        while ($row = mysqli_fetch_row($query))
            $result [] = [ "id" => $row[0], "site_url" => $row[1]];
        return $result;
    }

    public function get_countries($site_id){
        $sql = "SELECT SUM(clicks) as clicks, `country` FROM `data` 
WHERE `site_id` = $site_id 
GROUP BY country HAVING SUM(clicks) > 0 ORDER BY 1 DESC";
        $query = mysqli_query($this->connection, $sql);
        $result = [];
        while ($row = mysqli_fetch_assoc($query)){
            $result [] = [
                "clicks" => $row['clicks'],
                "country" => $row['country'],
            ];
        }
        return $result;
    }

    public function get_pages($site_id){
        $sql = "SELECT SUM(clicks) as clicks, `page` FROM `data` 
WHERE `site_id` = $site_id 
GROUP BY page ORDER BY 1 DESC";
        $query = mysqli_query($this->connection, $sql);
        $result = [];
        while ($row = mysqli_fetch_assoc($query)){
            $result [] = [
                "clicks" => $row['clicks'],
                "page" => $row['page'],
            ];
        }
        return $result;
    }

    public function runSql($sql){
        $query = mysqli_query($this->connection, $sql);
        $result = [];
        while ($row = mysqli_fetch_assoc($query)){
            $result [] = [
                "date" => $row['date'],
                "country" => $row['country'],
                "device" => $row['device'],
                "query" => $row['query'],
                "page" => $row['page'],
                "clicks" => $row['clicks'],
                "impressions" => $row['impressions'],
                "ctr" => $row['ctr'],
                "position" => $row['position'],
            ];
        }
        return $result;
    }
}