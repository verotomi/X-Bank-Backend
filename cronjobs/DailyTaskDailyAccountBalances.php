<?php
$config = require '../src/config.php';

/**
 * Bankszámlák egyenlegeinek a frissítését végzi
 */
function updateBalances()
{
  $dt = new DateTime("now", new DateTimeZone('Europe/Budapest'));
  $current_time = $dt->format('Y/m/d H:i:s');
  $dt2 = new DateTime("now", new DateTimeZone('Europe/Budapest'));
  $dt2->modify('-2 hours');
  $generated_on_time = $dt2->format('Y/m/d H:i:s');
  $current_time = $dt->format('Y/m/d H:i:s');
  global $config;
  $conn = mysqli_connect($config["DB_HOST"], $config["DB_USER"], $config["DB_PASS"], $config["DB_DATABASE"]);
  if (!$conn) {
    exit("Kapcsoldási hiba: " . mysqli_connect_error());
  }
  $sql = "SELECT id, balance
          FROM bank_accounts
          WHERE status = 'Active'";
  echo $sql;
  echo ("<br/>");
  $result = mysqli_query($conn, $sql);
  while ($row = mysqli_fetch_assoc($result)) {
    echo ("<br/>");
    $sql = "INSERT INTO " . TABLE_NAME_DAILY_ACCOUNT_BALANCES . " (
              id_bank_account_number,
              closing_balance,
              date,
              generated_on
              ) 
            VALUES (
              " . $row['id'] . ",
              " . $row['balance'] . ",
              '" . $generated_on_time . "',
              '" . $current_time . "'
    )";
    echo strtotime(date('Y-m-d'));
    echo $sql;
    echo ("<br/><br />");
    if (mysqli_query($conn, $sql)) {
      echo "Sikeres egyenleg frissítés!<br /><br />";
    } else {
      echo "Sikertelen egyenleg frissítés!<br /><br />";
    }
  }
}
echo ("Start...</br></br>");

updateBalances();
