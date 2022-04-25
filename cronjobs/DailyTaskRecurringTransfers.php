<?php
$config = require '../src/config.php';

/**
 * Az aktuálissá váló állandó átutalásokkal kapcsolatos teendőket végzi
 */
function handleRecurringTransfers()
{
  $dt = new DateTime("now", new DateTimeZone('Europe/Budapest'));
  $current_time = $dt->format('Y/m/d H:i:s');
  global $config;
  $conn = mysqli_connect($config["DB_HOST"], $config["DB_USER"], $config["DB_PASS"], $config["DB_DATABASE"]);  if (!$conn) {
    exit("Kapcsoldási hiba: " . mysqli_connect_error());
  }
  $sql = "SELECT id, id_user, id_bank_account_number, name, type, direction, reference_number, currency, amount, partner_name, partner_account_number, comment, arrived_on, status, last_fulfilled, frequency, days 
    	    FROM " . TABLE_NAME_RECURRING_TRANSFERS . " 
          WHERE status = 'Active' ORDER by id";
  echo $sql;
  echo ("<br/>");
  $result1 = mysqli_query($conn, $sql);
  while ($row1 = mysqli_fetch_assoc($result1)) {
    if (
      ($row1['frequency'] == 'Every day') or
      ($row1['days'] == date('l') and $row1['frequency'] == 'Every week') or
      ($row1['days'] == date('d') and $row1['frequency'] == 'Every month')
    ) {
      echo ("<br/>");
      echo ("<br/>");
      echo $row1['id'];
      echo (" ");
      echo $row1['id_user'];
      echo ("<br/>");
      $sql = "SELECT balance FROM " . TABLE_NAME_BANK_ACCOUNTS . " WHERE id = " . $row1['id_bank_account_number'];
      echo $sql;
      echo ("<br/><br />");
      $result2 = mysqli_query($conn, $sql);
      $row2 = mysqli_fetch_assoc($result2);
      $balance = $row2['balance'];
      if ($result2) {
        echo "<br/>Sikeres egyenleg lekérdezés! <br/>";
      } else {
        echo "<br/>Sikertelen egyenleg lekérdezés!<br/>";
      }
      echo "Balance " . $balance;
      echo ("<br/><br />");
      $fee = ($row1['currency'] == 'Forint' ? FLOOR(($row1['amount'] * 0.004 + 100)) : ($row1['amount'] * 0.004 + 0.5));
      if ($balance >= $row1['amount'] + $fee) {
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
                  " . $row1['id_user'] . ",
                  " . $row1['id_bank_account_number'] . ",
                  'recurring transfer',
                  'out',
                  " . rand(100000000, 999999999) . ",
                  '" . $row1['currency'] . "',
                  " . $row1['amount'] . ",
                  '" . $row1['partner_name'] . "',
                  '" . $row1['partner_account_number'] . "',
                  '" . $row1['comment'] . "',
                  '" . $current_time . "'
        )";
        $sender_id = $row1['id_user'];
        $sender_bank_account_id = $row1['id_bank_account_number'];
        $target_comment = $row1['comment'];
        echo $sql;
        echo ("<br/><br />");
        if (mysqli_query($conn, $sql)) {
          echo "Sikeres állandó megbízás végrehajtás!<br /><br />";
        } else {
          echo "Sikertelen állandó megbízás végrehajtás!<br /><br />";
        }
        $fee = ($row1['currency'] == 'Forint' ? FLOOR(($row1['amount'] * 0.004 + 100)) : ($row1['amount'] * 0.004 + 0.5));
        $comment = $row1['partner_name'] . ' ' . $row1['amount'] . ' ' . $row1['currency'];
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
                  " . $row1['id_bank_account_number'] . ",
                  'recurring transfer fee',
                  'out',
                  " . rand(100000000, 999999999) . ",
                  '" . $row1['currency'] . "',
                  " . $fee . ",
                  'X Bank Limited',
                  '" . $comment . "',
                  '" . $current_time . "'
        )";
        $sender_currency = $row1['currency'];
        $amount = $row1['amount'];
        echo $sql;
        echo ("<br/><br />");
        if (mysqli_query($conn, $sql)) {
          echo "Sikeres tranzakciós díj felszámítás!<br /><br />";
        } else {
          echo "Sikertelen tranzakciós díj felszámítás!<br /><br />";
        }
        $sql = "UPDATE " . TABLE_NAME_RECURRING_TRANSFERS . " SET last_fulfilled = '" . date('Y-m-d H:i:s') . "' WHERE id = " . $row1['id'];
        echo $sql;
        if (mysqli_query($conn, $sql)) {
          echo "<br /><br />Állandó megbízás frissítve!<br /><br />";
        } else {
          echo "<br /><br />Állandó megbízás frissítés sikertelen!<br /><br />";
        }
        $sql = "SELECT ba.id AS 'ba_id', ba.id_user AS 'ba_id_user', ba.currency AS 'ba_currency' FROM " . TABLE_NAME_BANK_ACCOUNTS . " ba LEFT JOIN " . TABLE_NAME_TRANSACTIONS . " t ON ba.id = t.id_bank_account_number WHERE ba.number = '" . $row1['partner_account_number'] . "'";
        echo $sql;
        echo ("<br/>");
        $result3 = mysqli_query($conn, $sql);
        while ($row3 = mysqli_fetch_assoc($result3)) {
          $target_id_bank_account_number = $row3['ba_id'];
          $target_id_user = $row3['ba_id_user'];
          $target_currency = $row3['ba_currency'];
        }
        if ($result3) {
          echo "<br/>Sikeres adat kiolvasás 1! <br/>";
        } else {
          echo "<br/>Sikertelen adat kiolvasás 1!<br/>";
        }
        $sql = "SELECT firstname, lastname, ba.number AS ba_number FROM " . TABLE_NAME_BANK_ACCOUNTS . " ba LEFT JOIN " . TABLE_NAME_USERS . " u ON ba.id_user = u.id WHERE ba.id_user = " . $sender_id . " AND ba.id = " . $sender_bank_account_id;
        echo ("<br/>");
        echo $sql;
        echo ("<br/>");
        $result4 = mysqli_query($conn, $sql);
        while ($row4 = mysqli_fetch_assoc($result4)) {
          $target_partner_name = $row4['lastname'] . " " . $row4['firstname'];
          $target_partner_account_number = $row4['ba_number'];
        }
        if ($result4) {
          echo "<br/>Sikeres adat kiolvasás 2! <br/>";
        } else {
          echo "<br/>Sikertelen adat kiolvasás 2!<br/>";
        }
        if ($sender_currency == $target_currency) {
          $target_amount = $amount;
        } else {
          $conn = mysqli_connect($config["DB_HOST"], $config["DB_USER"], $config["DB_PASS"], $config["DB_DATABASE"]);
          if (!$conn) {
            exit("Kapcsoldási hiba: " . mysqli_connect_error());
          }
          $sql = "SELECT buy, sell FROM " . TABLE_NAME_FOREIGN_CURRENCIES . " WHERE name = 'EUR'";
          $result7 = mysqli_query($conn, $sql);
          $row7 = mysqli_fetch_assoc($result7);
          $foreigncurrency_sell = $row7["sell"];
          $foreigncurrency_buy = $row7["buy"];
          if ($sender_currency == "Euro") {
            $target_amount = intval($amount * $foreigncurrency_buy);
          }
          if ($sender_currency == "Forint") {
            $target_amount = $amount / $foreigncurrency_sell;
          }
        }
        $target_reference_number = rand(100000000, 999999999);
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
                  " . $target_id_user . ",
                  " . $target_id_bank_account_number . ",
                  'incoming transfer',
                  'in',
                  " . $target_reference_number . ",
                  '" . $target_currency . "',
                  " . $target_amount . ",
                  '" . $target_partner_name . "',
                  '" . $target_partner_account_number . "',
                  '" . $target_comment . "',
                  '" . $current_time . "'
        )";
        echo ("<br/>");
        echo $sql;
        echo ("<br/>");
        $result5 = mysqli_query($conn, $sql);
        if ($result5) {
          echo "<br/>Sikeres tranzakció beillesztés! <br/>";
        } else {
          echo "<br/>Sikertelen tranzakció beillesztés!";
        }
      } else {
        echo "<br/>Nincs elég pénz! <br/>";
      }
    }
  }
}
echo ("Start...</br></br>");

handleRecurringTransfers();
