<?php

use Middlewares\AuthMiddleware;
use Models\Atms;
use Models\BankAccounts;
use Models\Beneficiaries;
use Models\Branches;
use Models\CreditCards;
use Models\Currencies;
use Models\ForeignCurrencies;
use Models\RecurringTransfers;
use Models\Savings;
use Models\SavingTypes;
use Models\Statements;
use Models\Token;
use Models\Transactions;
use Models\Users;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

include_once __DIR__ . '/scripts.php';

$twig = Twig::create(__DIR__ . '/../templates', ['cache' => false]);
$app->get('/statistics', function ($request, $response, $args) {
  $view = Twig::fromRequest($request);
  $bankaccounts = BankAccounts::all();
  $savings = Savings::where('savings.status', '=', "Active")
    ->leftJoin('saving_types', 'savings.id_type', '=', 'saving_types.id')
    ->leftJoin('bank_accounts', 'savings.id_bank_account', '=', 'bank_accounts.id')
    ->select('*', 'savings.id as saving_id', 'savings.status as saving_status', 'saving_types.type', 'saving_types.duration', 'savings.expire_date', 'saving_types.rate', 'savings.amount', 'bank_accounts.currency', 'bank_accounts.number')
    ->get();
  $creditcards = CreditCards::where('credit_cards.id_user', '!=', "9999")
    ->leftJoin('bank_accounts', 'credit_cards.id_bank_account', '=', 'bank_accounts.id')
    ->select('*', 'credit_cards.status as credit_card_status', 'credit_cards.id as creditcard_id', 'credit_cards.type as creditcard_type', 'credit_cards.number as creditcard_number')
    ->get();
  $view->render($response, 'statistics.twig', [
    'bankaccounts' => $bankaccounts,
    'savings' => $savings,
    'creditcards' => $creditcards,
  ]);
  updateToken($request, $response);
  return $response
    ->withHeader('Content-type', 'application/json')
    ->withStatus(200);
})
  ->add(new AuthMiddleware());
$app->add(TwigMiddleware::create($app, $twig));

return function (App $app) {
  /**
   * CORS hiba kezelése
   */
  $app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
  });

  /**
   * CORS hiba kezelése
   */
  $app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
      ->withHeader('Access-Control-Allow-Origin', '*')
      ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
      ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
  });

  $app->get('/', function (Request $request, Response $response, $args) {
    $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NOT_AUTHORIZED]);
    $response->getBody()->write($kimenet);
    return $response
      ->withHeader('Content-type', 'application/json')
      ->withStatus(401);
  });

  /**
   * Hitelesítés nélkül használható végpontok csoportja
   */
  $app->group("", function (RouteCollectorProxy $group) {
    $group->get('/getcurrencies', function (Request $request, Response $response, $args) {
      $currencies = Currencies::all();
      $kimenet = json_encode($currencies);
      $response->getBody()->write($kimenet);
      return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
    });

    $group->get('/getforeigncurrencies', function (Request $request, Response $response, $args) {
      $currencies = ForeignCurrencies::all();
      $kimenet = json_encode($currencies);
      $response->getBody()->write($kimenet);
      return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
    });

    $group->get('/getbranches', function (Request $request, Response $response, $args) {
      $branches = Branches::all();
      $kimenet = json_encode($branches);
      $response->getBody()->write($kimenet);
      return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
    });

    $group->get('/getatms', function (Request $request, Response $response, $args) {
      $atms = Atms::all();
      $kimenet = json_encode($atms);
      $response->getBody()->write($kimenet);
      return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
    });

    $group->post('/login', function (Request $request, Response $response, $args) {
      $dt = new DateTime("now", new DateTimeZone('Europe/Budapest'));
      $current_time = $dt->format('Y/m/d H:i:s');
      $loginData = json_decode($request->getBody(), true);
      $password = $loginData['password'];
      $netbankId = $loginData['netbankId'];
      $user = Users::where('netbank_id', $netbankId)->first();
      if ($user === NULL) {
        $kimenet = json_encode(['error' => RESPONSE_UNSUCCESFUL_LOGIN]);
        $response->getBody()->write($kimenet);
        return $response
          ->withHeader('Content-type', 'application/json')
          ->withStatus(404);
      }
      if (!password_verify($password, $user->password)) {
        $kimenet = json_encode(['error' => RESPONSE_UNSUCCESFUL_LOGIN]);
        $response->getBody()->write($kimenet);
        return $response
          ->withHeader('Content-type', 'application/json')
          ->withStatus(404);
      }
      $token = new Token();
      $token->user_id = $user->id;
      $token->token = bin2hex(random_bytes(64));
      $token->save();
      $kimenet = json_encode($user);
      $response->getBody()->write(json_encode([
        "id" => $user->id,
        "firstname" => $user->firstname,
        "lastname" => $user->lastname,
        "netbank_id" => $user->netbank_id,
        "created_on" => $user->created_on,
        "last_login" => $user->last_login,
        "token" => $token->token,
      ]));
      $user->update(['last_login' => $current_time]);
      return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
    });

    $group->post('/logout', function (Request $request, Response $response, $args) {
      $data = $request->getParsedBody();
      $netbankId = $data['netbank_id'];
      $user = Users::where('netbank_id', $netbankId)->first();
      if ($user === NULL) {
        $kimenet = json_encode(['error' => RESPONSE_UNSUCCESFUL_LOGIN]);
        $response->getBody()->write($kimenet);
        return $response
          ->withHeader('Content-type', 'application/json')
          ->withStatus(404);
      }
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
        ->delete();
      $kimenet = json_encode(['Message' => "Sikeres kilépés"]);
      $response->getBody()->write($kimenet);
      return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(201);
    });
  });

  /**
   * Hitelesítéshez kötött végpontok csoportja
   */
  $app->group("", function (RouteCollectorProxy $group) {
    $group->post('/getaccountbalances', function (Request $request, Response $response, $args) {
      $data = json_decode($request->getBody(), true);
      $bankaccounts = BankAccounts::where('id_user', '=', $data['id_user'])
        ->get();
      if ($bankaccounts === NULL) {
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
        $response->getBody()->write($kimenet);
        return $response
          ->withHeader('Content-type', 'application/json')
          ->withStatus(404);
      }
      $response->getBody()->write(json_encode($bankaccounts));
      updateToken($request, $response);
      return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
    })->add(new AuthMiddleware());;

    $group->put('/changepassword', function (Request $request, Response $response) {
      $data = $request->getParsedBody();
      $oldPassword = $data['old_password'];
      $user = Users::where('netbank_id', $data['netbank_id'])->first();
      if ($user === NULL) {
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
        $response->getBody()->write($kimenet);
        return $response
          ->withHeader('Content-type', 'application/json')
          ->withStatus(404);
      }
      if (!password_verify($oldPassword, $user->password)) {
        $kimenet = json_encode(['error' => RESPONSE_UNSUCCESFUL_PASSWORD_CHANGE]);
        $response->getBody()->write($kimenet);
        return $response
          ->withHeader('Content-type', 'application/json')
          ->withStatus(404);
      }
      $data = json_decode($request->getBody(), true);
      $user = Users::where('netbank_id', $data['netbank_id'])->first()
        ->update(['password' => password_hash($data['password'], PASSWORD_DEFAULT)]);
      $response->getBody()->write(json_encode($user));
      updateToken($request, $response);
      return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
    });

    $group->post('/getstatements', function (Request $request, Response $response, $args) {
      $data = json_decode($request->getBody(), true);
      $statements = Statements::where('bank_accounts.id_user', '=', $data['id_user'])
        ->leftJoin('bank_accounts', 'account_statements.id_bank_account', '=', 'bank_accounts.id')
        ->select(
          '*',
          'account_statements.id as account_statements_id',
          'bank_accounts.type',
          'bank_accounts.number as bank_accounts_number',
          'account_statements.number as account_statements_number',
        )
        ->orderBy('account_statements.id', 'DESC')
        ->get();
      if ($statements === NULL) {
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
        $response->getBody()->write($kimenet);
        return $response
          ->withHeader('Content-type', 'application/json')
          ->withStatus(404);
      }
      updateToken($request, $response);
      $response->getBody()->write(json_encode($statements));
      return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
    });

    $group->post('/getaccounthistory', function (Request $request, Response $response, $args) {
      $data = json_decode($request->getBody(), true);
      if ($data['direction'] == "all") {
        $history = Transactions::where('id_bank_account_number', '=', $data['id_bank_account_number'])
          ->where('arrived_on', '>=', $data['from'])
          ->where('arrived_on', '<=', $data['to'])
          ->leftJoin('bank_accounts', 'transactions.id_bank_account_number', '=', 'bank_accounts.id')
          ->select(
            '*',
            'transactions.id as transaction_id',
            'transactions.type as transaction_type',
          )
          ->orderBy('transactions.id', 'DESC')
          ->get();
      } else {
        $history = Transactions::where('id_bank_account_number', '=', $data['id_bank_account_number'])
          ->where('direction', '=', $data['direction'])
          ->where('arrived_on', '<', $data['to'])
          ->where('arrived_on', '>', $data['from'])
          ->leftJoin('bank_accounts', 'transactions.id_bank_account_number', '=', 'bank_accounts.id')
          ->select(
            '*',
            'transactions.id as transaction_id',
            'transactions.type as transaction_type',
          )
          ->orderBy('transactions.id', 'DESC')
          ->take(100)
          ->get();
      }
      updateToken($request, $response);
      $response->getBody()->write(json_encode($history));
      return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
    });

    $group->post('/getbeneficiaries', function (Request $request, Response $response, $args) {
      $data = json_decode($request->getBody(), true);
      $beneficiaries = Beneficiaries::where('id_user', '=', $data['id_user'])
        ->get();
      if ($beneficiaries === NULL) {
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
        $response->getBody()->write($kimenet);
        return $response
          ->withHeader('Content-type', 'application/json')
          ->withStatus(404);
      }
      updateToken($request, $response);
      $response->getBody()->write(json_encode($beneficiaries));
      return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
    });

    $group->get('/getsavingtypes', function (Request $request, Response $response, $args) {
      $savingTypes = SavingTypes::all();
      $response->getBody()->write(json_encode($savingTypes));
      updateToken($request, $response);
      return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
    });

    $group->post('/getsavings', function (Request $request, Response $response, $args) {
      $data = json_decode($request->getBody(), true);
      $savings = Savings::where('savings.id_user', '=', $data['id_user'])
        ->leftJoin('saving_types', 'savings.id_type', '=', 'saving_types.id')
        ->leftJoin('bank_accounts', 'savings.id_bank_account', '=', 'bank_accounts.id')
        ->select('*', 'savings.id as saving_id', 'savings.status as saving_status', 'saving_types.type', 'savings.expire_date', 'saving_types.rate', 'savings.amount', 'bank_accounts.currency', 'bank_accounts.number', 'bank_accounts.type as bank_account_type')
        ->get();
      updateToken($request, $response);
      $response->getBody()->write(json_encode($savings));
      return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
    });

    $group->post('/createtransferonetime', function (Request $request, Response $response) {
      $dt = new DateTime("now", new DateTimeZone('Europe/Budapest'));
      $current_time = $dt->format('Y/m/d H:i:s');
      $data = $request->getParsedBody();
      areTheseParametersAvailable(array('id_user', 'id_bank_account_number', 'currency', 'amount', 'partner_name', 'partner_account_number'), $data); // a comment mezőtkiszedtem innen , mert annak nem kötelező lennie
      $balance = getBalance($request, $response, $data['id_bank_account_number']);
      $fee = ($data['currency'] == 'Forint' ? FLOOR(($data['amount'] * 0.004 + 100)) : ($data['amount'] * 0.004 + 0.5));
      if ($balance >= intval($data['amount']) + $fee) {
        $data['arrived_on'] = $current_time;
        $data['status'] = "Active";
        $data['reference_number'] = rand(100000000, 999999999);
        $data['type'] = "outgoing transfer";
        $data['direction'] = "out";
        $transaction = Transactions::create($data);
        $transaction->save();
        $response->getBody()->write(json_encode($transaction));
        createTransferFeeTransaction($request, $response, $data, $fee);
        ob_start();
        try {
          createNewIncomingTransaction($request, $response, $data);
        } catch (\Throwable $th) {
        }
        ob_end_clean();
        updateToken($request, $response);
        return $response
          ->withHeader('Content-type', 'application/json')
          ->withStatus(201);
      } else {
        updateToken($request, $response);
        $kimenet = json_encode(['error' => RESPONSE_INSUFFICIENT_BALANCE]);
        $response->getBody()->write($kimenet);
        return $response
          ->withHeader('Content-type', 'application/json')
          ->withStatus(404);
      }
    });

    $group->post('/createbeneficiary', function (Request $request, Response $response, $args) {
      $data = $request->getParsedBody();
      areTheseParametersAvailable(array('id_user', 'name', 'partner_name', 'partner_account_number'), $data);
      $beneficiaryold = Beneficiaries::where('id_user', '=', $data['id_user'])
        ->where('name', '=', $data['name'])
        ->get()
        ->toArray();
      if (count($beneficiaryold) == 0) {
        $data['status'] = "Active";
        $beneficiary = Beneficiaries::create($data);
        $beneficiary->save();
        $response->getBody()->write(json_encode($beneficiary));
        updateToken($request, $response);
        return $response
          ->withHeader('Content-type', 'application/json')
          ->withStatus(201);
      } else {
        $kimenet = json_encode(['error' => RESPONSE_EXSISTING_BENEFICIARY]);
        $response->getBody()->write($kimenet);
        updateToken($request, $response);
        return $response
          ->withHeader('Content-type', 'application/json')
          ->withStatus(404);
      }
    });

    $group->post('/insertsaving', function (Request $request, Response $response, $args) {
      $data = $request->getParsedBody();
      $dt = new DateTime("now", new DateTimeZone('Europe/Budapest'));
      $current_time = $dt->format('Y/m/d H:i:s');
      $expire_date = $dt->add(new DateInterval('PT' . 60 * 24 * $data['duration'] . 'M'));
      $expdate2 = $expire_date->format('Y/m/d');
      areTheseParametersAvailable(array('id_user', 'id_bank_account', 'id_type', 'amount'), $data);
      $balance = getBalance($request, $response, $data['id_bank_account']);
      if ($balance >= intval($data['amount'])) {
        $data['status'] = "Active";
        $data['arrived_on'] = $current_time;
        $data['expire_date'] = $expdate2;
        $data['reference_number'] = rand(100000000, 999999999);
        $saving = Savings::create($data);
        $saving->save();
        $response->getBody()->write(json_encode($saving));
        updateToken($request, $response);
        return $response
          ->withHeader('Content-type', 'application/json')
          ->withStatus(201);
      } else {
        $kimenet = json_encode(['error' => RESPONSE_INSUFFICIENT_BALANCE_2]);
        updateToken($request, $response);
        $response->getBody()->write($kimenet);
        return $response
          ->withHeader('Content-type', 'application/json')
          ->withStatus(404);
      }
    });

    $group->put('/breakdeposit', function (Request $request, Response $response, $args) {
      $data = $request->getParsedBody();
      areTheseParametersAvailable(array('id'), $data);
      Savings::where('id', '=', $data['id'])
        ->update([
          'status' => "Breaked"
        ]);
      $savings = Savings::where('savings.id_user', '=', $data['id_user'])
        ->leftJoin('saving_types', 'savings.id_type', '=', 'saving_types.id')
        ->leftJoin('bank_accounts', 'savings.id_bank_account', '=', 'bank_accounts.id')
        ->select('*', 'savings.id as saving_id', 'savings.status as saving_status', 'saving_types.type', 'savings.expire_date', 'saving_types.rate', 'savings.amount', 'bank_accounts.currency', 'bank_accounts.number')
        ->get();
      $response->getBody()->write(json_encode($savings));
      updateToken($request, $response);
      return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
    });

    $group->put('/updatebeneficiary', function (Request $request, Response $response, $args) {
      $data = $request->getParsedBody();
      areTheseParametersAvailable(array('id', 'name', 'partner_name', 'partner_account_number'), $data);
      $data['status'] = "Active";
      $beneficiaryold = Beneficiaries::where('id_user', '=', $data['id_user'])
        ->where('name', '=', $data['name'])
        ->where('id', '!=', $data['id'])
        ->get()
        ->toArray();
      if (count($beneficiaryold) == 0) {
        $beneficiary = Beneficiaries::where('id', '=', $data['id'])
          ->update([
            'name' => $data['name'],
            'partner_name' => $data['partner_name'],
            'partner_account_number' => $data['partner_account_number'],
          ]);
        $response->getBody()->write(json_encode($beneficiary));
        updateToken($request, $response);
        return $response
          ->withHeader('Content-type', 'application/json')
          ->withStatus(201);
      } else {
        $kimenet = json_encode(['error' => RESPONSE_EXSISTING_BENEFICIARY]);
        $response->getBody()->write($kimenet);
        updateToken($request, $response);
        return $response
          ->withHeader('Content-type', 'application/json')
          ->withStatus(404);
      }
    });

    $group->delete('/deletebeneficiary', function (Request $request, Response $response, $args) {
      $data = $request->getParsedBody();
      $beneficiary = Beneficiaries::find($data['id']);
      $beneficiary->delete();
      $response->getBody()->write(json_encode($beneficiary));
      updateToken($request, $response);
      return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(201);
    });

    $group->post('/getrecurringtransfers', function (Request $request, Response $response, $args) {
      $data = json_decode($request->getBody(), true);
      $recurringTransfers = RecurringTransfers::where('recurring_transfers.id_user', '=', $data['id_user'])
        ->leftJoin('bank_accounts', 'recurring_transfers.id_bank_account_number', '=', 'bank_accounts.id')
        ->select('*', 'recurring_transfers.status as recurring_transfer_status', 'recurring_transfers.id as recurring_transfer_id')
        ->get();
      $response->getBody()->write(json_encode($recurringTransfers));
      updateToken($request, $response);
      return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
    });

    $group->post('/createrecurringtransfer', function (Request $request, Response $response, $args) {
      $dt = new DateTime("now", new DateTimeZone('Europe/Budapest'));
      $current_time = $dt->format('Y/m/d H:i:s');
      $data = $request->getParsedBody();
      areTheseParametersAvailable(array('id_user', 'id_bank_account_number', 'name', 'currency', 'amount', 'partner_name', 'partner_account_number', 'frequency'), $data); // a comment mezőtkiszedtem innen , mert annak nem kötelező lennie
      $recurringTransferOld = RecurringTransfers::where('id_user', '=', $data['id_user'])
        ->where('name', '=', $data['name'])
        ->get()
        ->toArray();
      if (count($recurringTransferOld) == 0) {
        $data['status'] = "Active";
        $data['reference_number'] = rand(100000000, 999999999);
        $data['arrived_on'] = $current_time;
        $data['type'] = "állandó átutalás";
        $data['direction'] = "out";
        $data['status'] = "Active";
        $recurringTransfer = RecurringTransfers::create($data);
        $recurringTransfer->save();
        $recurringTransfers = RecurringTransfers::where('recurring_transfers.id_user', '=', $data['id_user'])
          ->leftJoin('bank_accounts', 'recurring_transfers.id_bank_account_number', '=', 'bank_accounts.id')
          ->select('*', 'recurring_transfers.status as recurring_transfer_status', 'recurring_transfers.id as recurring_transfer_id')
          ->get();
        $response->getBody()->write(json_encode($recurringTransfers));
        updateToken($request, $response);
        return $response
          ->withHeader('Content-type', 'application/json')
          ->withStatus(200);
      } else {
        $kimenet = json_encode(['error' => RESPONSE_EXSISTING_RECURRINGTRANSFER]);
        $response->getBody()->write($kimenet);
        updateToken($request, $response);
        return $response
          ->withHeader('Content-type', 'application/json')
          ->withStatus(404);
      }
    });

    $group->put('/updaterecurringtransfer', function (Request $request, Response $response, $args) {
      $dt = new DateTime("now", new DateTimeZone('Europe/Budapest'));
      $current_time = $dt->format('Y/m/d H:i:s');
      $data = $request->getParsedBody();
      areTheseParametersAvailable(array('id_bank_account_number', 'name', 'currency', 'amount', 'partner_name', 'partner_account_number', 'status', 'frequency', 'id'), $data); // a comment mezőtkiszedtem innen , mert annak nem kötelező lennie
      $recurringTransferOld = RecurringTransfers::where('id_user', '=', $data['id_user'])
        ->where('name', '=', $data['name'])
        ->where('id', '!=', $data['id'])
        ->get()
        ->toArray();
      if (count($recurringTransferOld) == 0) {
        $recurringTransfer = RecurringTransfers::where('id', '=', $data['id'])
          ->update([
            'id_bank_account_number' => $data['id_bank_account_number'],
            'name' => $data['name'],
            'currency' => $data['currency'],
            'amount' => $data['amount'],
            'partner_name' => $data['partner_name'],
            'partner_account_number' => $data['partner_account_number'],
            'comment' => $data['comment'],
            'arrived_on' => $current_time,
            'status' => $data['status'],
            'frequency' => $data['frequency'],
            'days' => $data['days']
          ]);
        $recurringTransfers = RecurringTransfers::where('recurring_transfers.id_user', '=', $data['id_user'])
          ->leftJoin('bank_accounts', 'recurring_transfers.id_bank_account_number', '=', 'bank_accounts.id')
          ->select('*', 'recurring_transfers.status as recurring_transfer_status', 'recurring_transfers.id as recurring_transfer_id')
          ->get();
        $response->getBody()->write(json_encode($recurringTransfers));
        updateToken($request, $response);
        return $response
          ->withHeader('Content-type', 'application/json')
          ->withStatus(200);
      } else {
        $kimenet = json_encode(['error' => RESPONSE_EXSISTING_RECURRINGTRANSFER]);
        $response->getBody()->write($kimenet);
        updateToken($request, $response);
        return $response
          ->withHeader('Content-type', 'application/json')
          ->withStatus(404);
      }
    });

    $group->delete('/deleterecurringtransfer', function (Request $request, Response $response, $args) {
      $data = $request->getParsedBody();
      $recurringTransfer = RecurringTransfers::find($data['id']);
      $recurringTransfer->delete();
      $recurringTransfers = RecurringTransfers::where('recurring_transfers.id_user', '=', $data['id_user'])
        ->leftJoin('bank_accounts', 'recurring_transfers.id_bank_account_number', '=', 'bank_accounts.id')
        ->select('*', 'recurring_transfers.status as recurring_transfer_status', 'recurring_transfers.id as recurring_transfer_id')
        ->get();
      $response->getBody()->write(json_encode($recurringTransfers));
      updateToken($request, $response);
      return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
    });

    $group->post('/getcreditcards', function (Request $request, Response $response, $args) {
      $data = json_decode($request->getBody(), true);
      $creditCards = CreditCards::where('credit_cards.id_user', '=', $data['id_user'])
        ->leftJoin('bank_accounts', 'credit_cards.id_bank_account', '=', 'bank_accounts.id')
        ->select('*', 'credit_cards.status as credit_card_status', 'credit_cards.id as creditcard_id', 'credit_cards.type as creditcard_type', 'credit_cards.number as creditcard_number')
        ->get();
      $response->getBody()->write(json_encode($creditCards));
      updateToken($request, $response);
      return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
    });

    $group->put('/updatecreditcard', function (Request $request, Response $response, $args) {
      $data = $request->getParsedBody();
      areTheseParametersAvailable(array('id', 'status', 'limit_atm', 'limit_pos', 'limit_online'), $data);
      $beneficiary = CreditCards::where('id', '=', $data['id'])
        ->update([
          'status' => $data['status'],
          'limit_atm' => $data['limit_atm'],
          'limit_pos' => $data['limit_pos'],
          'limit_online' => $data['limit_online'],
          'id_bank_account' => $data['id_bank_account']
        ]);
      $creditCards = CreditCards::where('credit_cards.id_user', '=', $data['id_user'])
        ->leftJoin('bank_accounts', 'credit_cards.id_bank_account', '=', 'bank_accounts.id')
        ->select('*', 'credit_cards.status as credit_card_status', 'credit_cards.id as creditcard_id', 'credit_cards.type as creditcard_type', 'credit_cards.number as creditcard_number')
        ->get();
      $response->getBody()->write(json_encode($creditCards));
      updateToken($request, $response);
      return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
    });
  })->add(new AuthMiddleware());
};
