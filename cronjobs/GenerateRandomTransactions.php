<?php
$config = require '../src/config.php';

global $currency2;
function insert($id_user, $id_bank_account_number, $type, $direction, $reference_number, $currency, $amount, $partner_name, $partner_account_number, $comment, $arrived_on)
{
  global $conn;
  global $config;
  $conn = mysqli_connect($config["DB_HOST"], $config["DB_USER"], $config["DB_PASS"], $config["DB_DATABASE"]);
  if (!$conn) {
    exit("Kapcsoldási hiba: " . mysqli_connect_error());
  }
  $sql = "INSERT INTO transactions (id_user, id_bank_account_number, type, direction, reference_number, currency, amount, partner_name, partner_account_number, comment, arrived_on)
    	VALUES ($id_user, $id_bank_account_number, '$type', '$direction', $reference_number, '$currency', $amount, '$partner_name', '$partner_account_number', '$comment', '$arrived_on')";
  echo $sql;
  echo ("<br/>");
  $result = mysqli_query($conn, $sql);
  if ($result) {
    echo "Sikeres futtatás! <br/>";
  } else {
    echo "Sikertelen insert utasítás!<br/>";
  }
}

echo "Start... " . date('Y/m/d H:i:s') . "<br/>";
$rand1 = rand(1, 15);
$rand2 = rand(1, 32);
$sql2 = "SELECT 
          ba.currency
        FROM bank_accounts ba LEFT JOIN users u ON ba.id_user = u.id
        WHERE ba.id = " . $rand2 . " AND ba.id_user = " . $rand1;
echo $sql2;
$conn = mysqli_connect($config["DB_HOST"], $config["DB_USER"], $config["DB_PASS"], $config["DB_DATABASE"]);
$result2 = mysqli_query($conn, $sql2);
foreach ($result2 as $row2) {
  $currency2 = $row2['currency'];
  echo "..." . $currency2;
}
if ($result2) {
  echo "Sikeres lekérdezés! <br/>";
} else {
  echo "Sikertelen lekérdezés!<br/>";
}
echo " -> " . $currency2 . " " . $rand1 . " " . $rand2 . "</br>";
$dt = new DateTime("now", new DateTimeZone('Europe/Budapest'));
$current_time = $dt->format('Y/m/d H:i:s');
if ($currency2 == "Forint" || $currency2 == "Euro") {
  $amount2 = $currency2 == "Forint" ? rand(1000, 20000) : rand(10, 200);
  insert($rand1, $rand2, "incoming transfer", "in", rand(100000000, 999999999), $currency2, $amount2, "Random Generated", rand(10000000, 99999999) . "-" . rand(10000000, 99999999) . "-" . rand(10000000, 99999999), "Random comment", $current_time);
}
echo "... done!";
