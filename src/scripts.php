<?php

use Models\BankAccounts;
use Models\ForeignCurrencies;
use Models\Token;
use Models\Transactions;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Adatbázis műveletek végrehajtása előtt ellenőrzi, hogy a kérésben elküldésre kerültek-e a művelet végrehajtásához szükséges paraméterek.
 * @param array   $params   a szükséges paramétereket tartalmazó tömb
 * @param array   $data     a HTTP kérés törzsét tartalmazó tömb
 */

function areTheseParametersAvailable($params, $data)
{
  $available = true;
  $missingparams = "";
  if (!is_null($data)) {
    foreach ($params as $param) {
      if (!array_key_exists($param, $data)) {
        $available = false;
        $missingparams = $missingparams . ", " . $param;
      }
    }
  } else {
    $available = false;
    $missingparams = ", " . implode(", ", $params);
  };
  if (!$available) {
    $response = array();
    $response['error'] = true;
    $response['message'] = 'Parameters' . substr($missingparams, 1, strlen($missingparams)) . ' missing';
    echo json_encode($response);
    exit();
  }
}

/**
 * Egy bankszámlaszám aktuális egyenlegét adja vissza
 * @param int   $id   a kérdéses banszámlaszám id-je
 */
function getBalance(Request $request, Response $response2, $id)
{
  $bankaccount = BankAccounts::where('id', '=', $id)
    ->get()
    ->toArray();
  if ($bankaccount === NULL) {
    $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
    $response2->getBody()->write($kimenet);
    return $response2
      ->withHeader('Content-type', 'application/json')
      ->withStatus(404);
  }
  return $bankaccount[0]['balance'];
};

/**
 * HTTP kérés érkezésekor frissíti a megfelelő token 'updated_at' mezőjét
 */
function updateToken(Request $request, Response $response)
{
  $dt = new DateTime("now", new DateTimeZone('Europe/Budapest'));
  $current_time = $dt->format('Y/m/d H:i:s');
  $auth = $request->getHeader('Authorization');
  if (count($auth) !== 1) {
    throw new Exception('Hibás Authorization header');
  }
  $authArr = mb_split(' ', $auth[0]);
  if ($authArr[0] !== 'Bearer') {
    throw new Exception("Nem támogatott autentikációs módszer");
  }
  $tokenStr = $authArr[1];
  Token::where('token', $tokenStr)
    ->update(['updated_at' => $current_time]);
  return ("OK");
};

/**
 * Egy kimenő utaláshoz kapcsolódó banki költséget tartalmazó tranzakciót hoz létre
 * @param array   $data     a HTTP kérés törzsét tartalmazó tömb
 * @param float   $fee      az utalási díj összege
 */
function createTransferFeeTransaction(Request $request, Response $response, $data, $fee)
{
  $dt = new DateTime("now", new DateTimeZone('Europe/Budapest'));
  $current_time = $dt->format('Y/m/d H:i:s');
  $data['arrived_on'] = $current_time;
  $data['comment'] = $data['partner_name'] . " " . $data['amount'] . " " . $data['currency'] . ". Ref No: " . $data['reference_number'];
  $data['reference_number'] = rand(100000000, 999999999);
  $data['type'] = "transfer fee";
  $data['amount'] = $fee;
  $transaction = Transactions::create($data);
  $transaction->save();
  return ($response);
};

/**
 * Beérkező utalás esetén létrehozza a megfelelő tranzakciót
 * @param array   $data     a HTTP kérés törzsét tartalmazó tömb
 */
function createNewIncomingTransaction(Request $request, Response $response, $data)
{
  $dt = new DateTime("now", new DateTimeZone('Europe/Budapest'));
  $current_time = $dt->format('Y/m/d H:i:s');
  $target1 = BankAccounts::where('number', '=', $data['partner_account_number'])
    ->leftJoin('transactions', 'transactions.id_bank_account_number', '=', 'bank_accounts.id')
    ->where('bank_accounts.number', '=', $data['partner_account_number'])
    ->take(1)
    ->select(
      'bank_accounts.id',
      'bank_accounts.id_user',
      'bank_accounts.currency'
    )
    ->get()
    ->toArray();
  $target2 = BankAccounts::where('id_user', '=', $data['id_user'])
    ->leftJoin('users', 'users.id', '=', 'bank_accounts.id_user')
    ->where('bank_accounts.id', '=', $data['id_bank_account_number'])
    ->select(
      'users.firstname as firstname',
      'users.lastname as lastname',
      'bank_accounts.number as number',
    )
    ->get()
    ->toArray();
  if ($target1[0]['currency'] == $data['currency']) {
    $target_amount = $data['amount'];
  } else {
    $currency = ForeignCurrencies::where('name', "=", "EUR")
      ->select('sell', 'buy')
      ->get()
      ->toArray();
    if ($data['currency'] == "Euro") {
      $target_amount = intval($data['amount'] * $currency[0]['buy']);
    }
    if ($data['currency'] == "Forint") {
      $target_amount = $data['amount'] / $currency[0]['sell'];
    }
  }
  $data2 = [];
  $data2['id_user'] = $target1[0]["id_user"];
  $data2['id_bank_account_number'] = $target1[0]["id"];
  $data2['type'] = "incoming transfer";
  $data2['direction'] = "in";
  $data2['reference_number'] = rand(100000000, 999999999);
  $data2['currency'] = $target1[0]["currency"];
  $data2['amount'] = $target_amount;
  $data2['partner_name'] = $target2[0]['lastname'] . " " . $target2[0]['firstname'];
  $data2['partner_account_number'] = $target2[0]['number'];
  $data2['comment'] = $data['comment'];
  $data2['arrived_on'] = $current_time;
  $transaction = Transactions::create($data2);
  $transaction
    ->save();
  return ($response);
};
