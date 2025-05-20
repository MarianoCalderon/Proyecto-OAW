<?php
function get_db_conn(){
    try{
        $host = 'localhost';
        $port = 3306;
        $dbName = 'rss_feed_db';
        $uri = "mysql:host=$host;port=$port;dbname=$dbName";
    
        $username = 'root';
        $password = '';
    
        $conn = new PDO($uri, $username, $password);
        return $conn;
    }catch(PDOException $e){
        echo "Connection failed: " . $e->getMessage();
        return null;
    }
}
?>