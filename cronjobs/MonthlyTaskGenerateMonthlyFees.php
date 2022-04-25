<?php
$config = require '../src/config.php';

/**
 * A havi számlavezetési díjakhoz kapcsolódó tranzakciókat hozza létre a transactions táblában
 */
function monthlyFee()
{
  $dt = new DateTime("now", new DateTimeZone('Europe/Budapest'));
  $current_time = $dt->format('Y/m/d H:i:s');
  global $config;
  $conn = mysqli_connect($config["DB_HOST"], $config["DB_USER"], $config["DB_PASS"], $config["DB_DATABASE"]);
  if (!$conn) {
    exit("Kapcsoldási hiba: " . mysqli_connect_error());
  }
  $sql = "SELECT id, id_user, number, type, currency 
    	        FROM " . TABLE_NAME_BANK_ACCOUNTS . "
    	        WHERE status='Active'
    	        ";
  echo $sql;
  echo ("<br/>");
  $result = mysqli_query($conn, $sql);
  while ($row1 = mysqli_fetch_assoc($result)) {
    echo ("<br/>");
    $fee = ($row1['currency'] == 'Forint' ? 2990 : 9.90);
    $sql = "INSERT INTO " . TABLE_NAME_TRANSACTIONS . " (
                    id_user,
                    id_bank_account_number,
                    type,
                    direction,
                    reference_number,
                    currency,
                    amount,
                    partner_name,
                    comment,
                    arrived_on) 
                VALUES (
                    " . $row1['id_user'] . ",
                    " . $row1['id'] . ",
                    'monthly fee',
                    'out',
                    " . rand(100000000, 999999999) . ",
                    '" . $row1['currency'] . "',
                    " . $fee . ",
                    'X Bank Limited',
                    'Monthly fee',
                    '" . $current_time . "'
            )";
    echo $sql;
    echo ("<br/><br />");
    if (mysqli_query($conn, $sql)) {
      echo "Sikeres havi számlavezetési díj felszámítás!<br /><br />";
    } else {
      echo "Sikertelen havi számlavezetési díj felszámítás!<br /><br />";
    }
  }
  if ($result) {
    echo "<br/>Sikeres futtatás! <br/>";
  } else {
    echo "<br/>Sikertelen futtatás!";
  }
}
echo ("Start...</br></br>");

monthlyFee();
