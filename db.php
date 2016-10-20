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
            die('Error');
        return $this->connection->insert_id;
    }

    public function get_last_record_date($site_id){
        $sql = "SELECT `date` FROM `data` WHERE `site_id` = $site_id";
        return mysqli_query($this->connection, $sql)->fetch_object()->date;
    }
//"clicks"]=> float(1) ["impressions"]=> float(2) ["ctr"]=> float(0.5) ["position"]=>
    public function add_record($site_id, $date, $country, $device, $query, $page, $clicks, 
                               $impressions, $ctr, $position){
        $sql = "INSERT INTO `data` (`site_id`,`date`, `country`, `device`, `query`, `page`, 
`clicks`, `impressions`, `ctr`, `position`) 
        VALUES ($site_id,'$date', '$country', '$device', '$query', '$page', 
        $clicks, $impressions, $ctr, $position)";
        if (!mysqli_query($this->connection, $sql))
            die('Error');
    }
}