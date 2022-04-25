<?php
$config = require '../src/config.php';

class dbObj
{
  var $conn;

  /**
  *  A bankszámlakivonatok készítésénél használt osztályban az adatbázishoz való kapcsolódást végzi
  */
  function getConnstring()
  {
    global $config;
    $con = mysqli_connect($config["DB_HOST"], $config["DB_USER"], $config["DB_PASS"], $config["DB_DATABASE"]) or exit("Connection failed: " . mysqli_connect_error());
    if (mysqli_connect_errno()) {
      printf("Connect failed: %s\n", mysqli_connect_error());
      exit();
    } else {
      $this->conn = $con;
    }
    return $this->conn;
  }
}
