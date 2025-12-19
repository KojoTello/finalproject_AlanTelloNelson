<?php

if (getenv('MYSQL_URL')) {
    $url = parse_url(getenv('MYSQL_URL'));
    $servername = $url["host"];
    $username = $url["user"];
    $password = $url["pass"];
    $dbname = substr($url["path"], 1);
    $port = $url["port"];
    
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
} else {
  
    $conn = new mysqli("localhost", "root", "", "ashesi_review_db");
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
