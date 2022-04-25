<?php
include_once("MakePdfXbankConnection.php");
require_once '../fpdf/fpdf.php';

define("STATEMENT_NUMBER", date('Y/m', strtotime(date('Y-m') . " -1 month")));
define("STATEMENT_NUMBER_FOR_FILENAME", date('Y-m', strtotime(date('Y-m') . " -1 month")));
define("CREATED_ON", date("Y-m-d H:i:s"));
define("FROM", date('Y-m-d', strtotime(date('Y-m') . " -1 month")));
define("TO", date('Y-m-d', strtotime(date('Y-m') . " -1 days")));

/**
 * A bankszámlakivonatok generálását végző osztály
 */
class PDF extends FPDF
{
  function Header()
  {
    global $name;
    global $accountNumber;
    global $currency;
    global $type;
    $this->SetFont('Arial', 'B', 24);
    $this->SetFillColor(36, 96, 84);
    $this->SetTextColor(225);
    $this->Cell(95, 20, " " . "X Bank Limited", 0, 0, 'L', true);
    $this->SetFont('Arial', 'B', 14);
    $this->SetXY(105, 10);
    $this->Cell(95, 10, "Account statement" . " ", 0, 1, 'R', true);
    $this->SetXY(105, 20);
    $this->Cell(95, 10, STATEMENT_NUMBER . " ", 0, 1, 'R', true);
    $this->SetFont('Arial', '', 10);
    $name = iconv('UTF-8', 'windows-1252//TRANSLIT', $name);
    $this->Cell(95, 6, " " . "Account's owner:" . " " . $name, 0, 0, 'L', true);
    $this->Cell(95, 6, " " . "Period:" . " " . FROM . " - " . TO . " ", 0, 1, 'R', true);
    $this->Cell(95, 6, " " . "Account number:" . " " . $accountNumber, 0, 0, 'L', true);
    $this->Cell(95, 6, " " . "Created on:" . " " . CREATED_ON . " ", 0, 1, 'R', true);
    $this->Cell(95, 6, " " . "Account type:" . " " . $type, 0, 0, 'L', true);
    $this->Cell(95, 6, " " . "Currency:" . " " . $currency . " ", 0, 1, 'R', true);
    $this->Ln();
  }

  function Footer()
  {
    $this->SetY(-15);
    $this->SetFont('Arial', 'I', 8);
    $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
  }
}

function GetAccountData($id_bank_account)
{
  global $name;
  global $accountNumber;
  global $currency;
  global $type;
  global $id_user;
  global $firstname;
  $db = new dbObj();
  $connString =  $db->getConnstring();
  $result = mysqli_query(
    $connString,
    "SELECT 
      u.lastname, 
      u.firstname, 
      ba.number, 
      ba.type, 
      ba.currency,
      ba.id,
      ba.id_user
    FROM bank_accounts ba LEFT JOIN users u ON ba.id_user = u.id
    WHERE ba.id = " . $id_bank_account
  ) or exit("database error:" . mysqli_error($connString));
  foreach ($result as $row) {
    $name = $row['firstname'] . " " . $row['lastname'];
    $accountNumber = $row['number'];
    $currency = $row['currency'];
    $type = $row['type'];
    $id_bank_account = $row['id'];
    $id_user = $row['id_user'];
    $firstname = $row['firstname'];
  }
}

function GetOpeningBalance($id_bank_account)
{
  global $opening_balance;
  $db = new dbObj();
  $connString =  $db->getConnstring();
  $sql = "SELECT 
            closing_balance
          FROM daily_account_balances
          WHERE id_bank_account_number = " . $id_bank_account . " AND date = '" . date('Y-m-d', strtotime(date('Y-m') . " -1 month -1 days")) . "'"; // -2 nap volt itt, de hibás volt
  $result = mysqli_query(
    $connString,
    $sql
  ) or exit("database error:" . mysqli_error($connString));
  foreach ($result as $row) {
    $opening_balance = $row['closing_balance'];
  }
}

function CreateStatement($id_bank_account)
{
  $db = new dbObj();
  $connString =  $db->getConnstring();
  global $currency;
  global $accountNumber;
  global $opening_balance;
  global $firstname;
  $result = mysqli_query($connString, "
            SELECT 
              id, 
              arrived_on, 
              type, 
              reference_number, 
              amount, 
              balance, 
              partner_account_number, 
              comment, 
              currency, 
              direction, 
              partner_name
            FROM transactions WHERE id_bank_account_number = " . $id_bank_account . " AND DATE(arrived_on) >= '" . FROM . "' AND DATE(arrived_on) <= '" . TO . "' ORDER BY id") or exit("database error:" . mysqli_error($connString));
  $pdf = new PDF();
  $pdf->AddPage();
  $pdf->AliasNbPages();
  $pdf->SetFont('Arial', 'B', 10);
  $pdf->SetFillColor(238);
  $pdf->Cell(30, 8, "Date", "B", 0, 'L', false);
  $pdf->Cell(100, 8, "Description", "B", 0, 'L', false);
  $pdf->Cell(30, 8, "Amount", "B", 0, 'R', false);
  $pdf->Cell(30, 8, "Balance", "B", 1, 'R', false);
  $pdf->Cell(190, 1, "", "T", 1, 'L', false);
  $fill = false;
  $pdf->SetFillColor(238);
  $pdf->SetFont('Arial', '', 9.5);;
  $pdf->Ln();
  $pdf->SetTextColor(48);
  $balance = $opening_balance;
  $withdrawals = 0;
  $deposits = 0;
  foreach ($result as $row) {
    $pdf->Cell(30, 4.5, substr($row['arrived_on'], 0, 10), 0, 0, 'L', $fill);
    $pdf->Cell(100, 4.5, ucfirst($row['type']) . " (" . $row['reference_number'] . ") ", 0, 0, 'L', $fill);
    $amount = $row['amount'];
    if ($row['direction'] == "out") {
      $pdf->SetTextColor(192, 0, 0);
      $amount = -$amount;
      $withdrawals = $withdrawals + $amount;
    } else {
      $deposits = $deposits + $amount;
    }
    $balance = $balance + $amount;
    $pdf->Cell(30, 4.5, number_format($amount, 2, '.', ' '), 0, 0, 'R', $fill);
    $pdf->SetTextColor(48);
    if ($row['balance'] < 0) {
      $pdf->SetTextColor(192, 0, 0);
      $balance = -$balance;
    }
    $pdf->Cell(30, 4.5, number_format($balance, 2, '.', ' '), 0, 1, 'R', $fill);
    $pdf->SetTextColor(48);
    $pdf->SetFont('Arial', 'I', 9.5);
    $pdf->Cell(30, 4.5, "       " . substr($row['arrived_on'], 11, 19), 0, 0, 'L', $fill);
    $separator1 = $row['partner_account_number'] == NULL ? "" : " ";
    $separator2 = $row['partner_name'] == NULL ? "" : " ";
    $row2 = $row['partner_account_number'] . $separator1 . $row['partner_name'] . $separator2 . $row['comment'];
    $row2 = iconv('UTF-8', 'windows-1252//TRANSLIT', $row2);
    $shifter = 52;
    $pdf->Cell(100, 4.5, "   " . substr($row2, 0, $shifter), 0, 0, 'L', $fill);
    $pdf->Cell(30, 4.5, "", 0, 0, 'R', $fill);
    $pdf->Cell(30, 4.5, "", 0, 1, 'R', $fill);
    if (strlen($row2) > $shifter) {
      $pdf->Cell(30, 4.5, "", 0, 0, 'L', $fill);
      $pdf->Cell(100, 4.5, "   " . substr($row2, $shifter, $shifter * 2 - 2), 0, 0, 'L', $fill);
      $pdf->Cell(30, 4.5, "", 0, 0, 'R', $fill);
      $pdf->Cell(30, 4.5, "", 0, 1, 'R', $fill);
      $pdf->SetFont('Arial', '', 9.5);
    }
    $pdf->Cell(190, 1, "", 0, 1, 'L', $fill);
    $pdf->SetFont('Arial', '', 9.5);;
    $fill = !$fill;
  }
  $pdf->Ln(20);
  $pdf->SetX(130);
  $pdf->Cell(40, 5, "Opening balance:", "TL", 0, 'L', false);
  $pdf->Cell(0, 5, number_format($opening_balance, 2, '.', ' ') . " " . $currency, "TR", 0, 'R', false);
  $pdf->Ln(4);
  $pdf->SetX(130);
  $pdf->Cell(40, 5, "Deposits:", "L", 0, 'L', false);
  $pdf->Cell(0, 5, number_format($deposits, 2, '.', ' ') . " " . $currency, "R", 0, 'R', false);
  $pdf->Ln(4);
  $pdf->SetX(130);
  $pdf->Cell(40, 5, "Withdrawals:", "L", 0, 'L', false);
  $pdf->Cell(0, 5, number_format($withdrawals, 2, '.', ' ') . " " . $currency, "R", 0, 'R', false);
  $pdf->Ln(4);
  $pdf->SetX(130);
  $pdf->Cell(40, 5, "Closing balance:", "LB", 0, 'L', false);
  $pdf->Cell(0, 5, number_format($opening_balance  + $deposits + $withdrawals, 2, '.', ' ') . " " . $currency, "BR", 2, 'R', false);
  global $filename;
  $filename = 'Xbank-statement-' . STATEMENT_NUMBER_FOR_FILENAME . "-" . $accountNumber . '.pdf';
  $pdf->Output('F', '../Statements/' . $filename, true);
}

function InsertInfoIntoDataBase($id_user, $id_bank_account, $number, $filename)
{
  global $id_user;
  global $filename;
  global $currency;
  global $firstname;
  $db = new dbObj();
  $connString =  $db->getConnstring();
  $result = mysqli_query(
    $connString,
    " INSERT INTO account_statements (
        id_user,
        id_bank_account,
        number,
        filename)
      VALUES (
        " . $id_user . ",
        " . $id_bank_account . ",
        '" . STATEMENT_NUMBER . "',
        '" . $filename . "'
      )"
  ) or exit("database error:" . mysqli_error($connString));
  foreach ($result as $row) {
    $name = $row['firstname'] . " " . $row['lastname'];
    $accountNumber = $row['number'];
    $currency = $row['currency'];
    $type = $row['type'];
  }
}

function GetAllActiveAccounts()
{
  global $id_user;
  global $filename;
  $db = new dbObj();
  $connString =  $db->getConnstring();
  $result = mysqli_query(
    $connString,
    "SELECT id
    FROM bank_accounts
    WHERE status = 'Active'"
  ) or exit("database error:" . mysqli_error($connString));
  foreach ($result as $row) {
    GetOpeningBalance($row['id']);
    GetAccountData($row['id']);
    CreateStatement($row['id']);
    InsertInfoIntoDataBase($id_user, $row['id'], STATEMENT_NUMBER, $filename);
  }
}

GetAllActiveAccounts();
