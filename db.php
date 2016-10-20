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
    public function new_action($key, $ip)
    {
        $safe_key = mysqli_real_escape_string($this->connection, $key);
        $sql = "INSERT INTO actions (`key`,`ip`) VALUES ('$safe_key','$ip')";
        if (!mysqli_query($this->connection, $sql))
            die('Error');
        return $this->connection->insert_id;
    }
    public function is_wasted_key($key)
    {
        $safe_key = mysqli_real_escape_string($this->connection, $key);
        $sql = "SELECT * FROM actions WHERE `key` = '$safe_key'";
        $query = mysqli_query($this->connection, $sql);
        $rows = mysqli_num_rows($query);
        if( $rows > 0)
            return true;
        return false;
    }
    public function new_listening($key_id, $audio){
        $sql = "INSERT INTO listening (`actions_id`,`audio`) VALUES ('$key_id','$audio')";
        if (!mysqli_query($this->connection, $sql))
            die('Error');
    }
    public function downloaded($key_id){
        $sql = "UPDATE actions SET downloaded = 1 WHERE `id` = $key_id";
        if (!mysqli_query($this->connection, $sql))
            die('Error' . mysqli_error($this->connection));
    }
    public function audio_stats(){
        $result = "";
        $sql = "SELECT COUNT(*) as number, audio
                FROM listening l 
                INNER JOIN actions a
                ON a.id = l.actions_id
                WHERE `key` <> '" . self::CHECK_KEY . "'
                GROUP BY audio";
        $query = mysqli_query($this->connection, $sql);
        while($row = $query->fetch_assoc())
            $result .= '[' . '"audio '.$row['audio'].'"'.', '.$row['number'] . '],';
        return $result;
    }
    public function keys_and_downloads_per_day(){
        $result = "";
        $sql = "SELECT COUNT(*) as number, COALESCE(SUM(downloaded),0) as downloaded, DATE_FORMAT(created, '%e-%m-%Y') as created_date 
                FROM actions 
                WHERE `key` <> '" . self::CHECK_KEY . "'
                GROUP BY DATE_FORMAT(created, '%e-%m-%Y')
                ORDER BY created";
        $query = mysqli_query($this->connection, $sql);
        while($row = $query->fetch_assoc())
            $result .= '[' . '"'.$row['created_date'].'"'.', '.$row['number'].', '.$row['downloaded']. '],';
        return $result;
    }
}