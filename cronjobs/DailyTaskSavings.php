<?php
$config = require '../src/config.php';

/**
 * A lejáró megtakarításokkal kapcsolatos teendőket végzi
 */
function handleSavings()
{
  $dt = new DateTime("now", new DateTimeZone('Europe/Budapest'));
  $current_time = $dt->format('Y/m/d H:i:s');
  $current_date = $dt->format('Y/m/d');
  global $config;
  $conn = mysqli_connect($config["DB_HOST"], $config["DB_USER"], $config["DB_PASS"], $config["DB_DATABASE"]);
  if (!$conn) {
    die("Kapcsoldási hiba: " . mysqli_connect_error());
  }
  $sql = "SELECT s.id, s.id_user, id_bank_account, id_type, amount, expire_date, s.status, reference_number, arrived_on, st.type, st.rate, st.duration, ba.currency, ba.number 
          FROM " . TABLE_NAME_SAVINGS . " s 
          LEFT JOIN " . TABLE_NAME_SAVING_TYPES . " st 
          ON s.id_type = st.id 
          LEFT JOIN " . TABLE_NAME_BANK_ACCOUNTS . " 
            ba ON s.id_bank_account = ba.id 
          WHERE s.status='Active' ORDER by s.id";
  echo $sql;
  echo ("<br/>");
  $result = mysqli_query($conn, $sql);
  while ($row = mysqli_fetch_assoc($result)) {
    if (strtotime($row['expire_date']) == $current_date) {
      echo ("<br/>");
      $sql = "INSERT INTO " . TABLE_NAME_TRANSACTIONS . " (
                id_user,
                id_bank_account_number,
                type,
                direction,
                reference_number,
                currency,
                amount,
                partner_name,
                partner_account_number,
                comment,
                arrived_on) 
              VALUES (
                " . $row['id_user'] . ",
                " . $row['id_bank_account'] . ",
                'fund withdrawal',
                'in',
                " . rand(100000000, 999999999) . ",
                '" . $row['currency'] . "',
                " . $row['amount'] . ",
                'X Bank Limited',
                '" . $row['number'] . "',
                '" . $row['amount'] . " " . $row['currency'] . " fund withdrawal',
                '" . $arrived_on = $current_time . "'
      )";
      echo $sql;
      echo ("<br/><br />");
      if (mysqli_query($conn, $sql)) {
        echo "Sikeres tőke jóváírás!<br /><br />";
      } else {
        echo "Sikertelen tőke jóváírás!<br /><br />";
      }
      if ($row['currency'] == 'Forint') {
        $interest = round(($row['amount'] * $row['rate']) / 100 / 365 * $row['duration']);
      }
      if ($row['currency'] == 'Euro') {
        $interest = ($row['amount'] * $row['rate']) / 100 / 365 * $row['duration'];
      }
      if ($interest > 0) {
        $sql = "INSERT INTO " . TABLE_NAME_TRANSACTIONS . " (
                  id_user,
                  id_bank_account_number,
                  type,
                  direction,
                  reference_number,
                  currency,
                  amount,
                  partner_name,
                  partner_account_number,
                  comment,
                  arrived_on) 
                VALUES (
                  " . $row['id_user'] . ",
                  " . $row['id_bank_account'] . ",
                  'interest withdrawal',
                  'in',
                  " . rand(100000000, 999999999) . ",
                  '" . $row['currency'] . "',
                  " . $interest . ",
                  'X Bank Limited',
                  '" . $row['number'] . "',
                  '" . number_format((float)$interest, 2, '.', '') . " " . $row['currency'] . " interest withdrawal',
                  '" . $arrived_on = $current_time . "'
        )";
        echo $sql;
        if (mysqli_query($conn, $sql)) {
          echo "<br /><br />Sikeres tranzakció!<br />";
        } else {
          echo "<br /><br />Sikertelen tranzakció!<br />";
        }
      }
      $sql = "UPDATE " . TABLE_NAME_SAVINGS . " SET status = 'Expired' WHERE id = " . $row['id'];
      echo $sql;
      if (mysqli_query($conn, $sql)) {
        echo "<br /><br />Megtakarítás állapot frissítve!<br /><br />";
      } else {
        echo "<br /><br />Megtakarítás állapot frissítés sikertelen!<br /><br />";
      }
    };
  }
  if ($result) {
    echo "<br/>Sikeres futtatás! <br/>";
  } else {
    echo "<br/>Sikertelen futtatás!";
  }
}
echo ("Start...</br></br>");

handleSavings();
