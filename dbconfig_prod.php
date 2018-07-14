
<?php

 function conn() {
  $servername = "localhost";
  $username = "root";
  $password = "P@ssw0rd";
  $dbname = "barcoms";
  $port=3307;
  // Create connection
  try {
  $conn = new \PDO("mysql:host=$servername;port=$port;dbname=$dbname", $username, $password);
  $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
  } catch (Exception $ex) {
  //$conn = "Connection failed: " . $ex->getMessage();
    throw $ex;
    
  }
  return $conn;
  } 

/*function conn() {
    //$host = "localhost";
    //$user = "root";
    //$password = "P@ssw0rd";
    //$datbase = "barcoms";
    //mysql_connect($host, $user, $password);
    //mysql_select_db($datbase);
    $servername = "localhost";
    $username = "root";
    $password = "P@ssw0rd";
    $dbname = "barcoms";
    //$password = "P@ssw0rd";
    //$dbname = "barcoms";
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } else {
        //echo "Connected successfully";
        return $conn;
    }
}*/

?>