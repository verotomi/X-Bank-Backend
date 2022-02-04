<?php

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Models\Users;
use Models\Currencies;
use Models\Branches;
use Models\Atms;
use Models\ForeignCurrencies;
use Models\Token;
use Middlewares\AuthMiddleware;
use Models\BankAccounts;
use Models\Beneficiaries;
use Models\Savings;
use Models\Statements;
use Models\Transactions;
use Slim\Routing\RouteCollectorProxy;

function isTheseParametersAvailable($params, $data){
    $available = true; 
    $missingparams = "";
    if(!is_null($data)){
       foreach($params as $param){
            if (!array_key_exists($param, $data)){
                $available = false; 
                $missingparams = $missingparams . ", " . $param;
            }
        }
    } else {
        $available = false;
        $missingparams = ", " . implode(", ", $params);

    };
    if(!$available){
        $response = array(); 
        $response['error'] = true; 
        $response['message'] = 'Parameters' . substr($missingparams, 1, strlen($missingparams)) . ' missing';
        echo json_encode($response);
        die();
    }
}

return function(App $app){
    $app->options('/{routes:.+}', function ($request, $response, $args) {
        return $response;
    });
    
    $app->add(function ($request, $handler) {
        $response = $handler->handle($request);
        return $response
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
    });
    
    $app->get('/', function (Request $request, Response $response, $args){
        $kimenet = json_encode(['error'=> RESPONSE_MESSAGE_NOT_AUTHORIZED]);
        $response->getBody()->write($kimenet);
        return $response
            ->withHeader ('Content-type', 'application/json')
            ->withStatus(401);
    });

    /**
     * regisztrálás nálam nem lesz, törlendő!
     */
    /*$app->post('/register', function(Request $request, Response $response, $args){
        $userData = json_decode($request->getBody(), true);
        $email = $userData['email'];
        $user = Users::where('email', $email)->first();
        if(!$user == null){  
            if($user->email == $email){
                $response->getBody()->write("Ilyen email címmel már van felhasználó az adatbázisban");
                return $response;
            }
        }
        // validáció
            /* (validation.php) < use Petrik\Loginapp\Validation;
                email: 
                    valódi email
                    van-e ilyen email
                jelszó:
                    minimum 6 karakter
                    maximum 2 karakter
                    tartalmazzon minimum
                        1 nagy betűt
                        1 kis betűt
                        1 számot
        
        $user = new Users();
        $user->email = $userData['email'];
        $user->password = password_hash($userData['password'], PASSWORD_DEFAULT);
        $user->save();
        $response->getBody()->write($user->toJson());
        return $response->withHeader('Content-Type', 'application/json')
                ->withStatus(201);
    });*/

    $app->post('/login', function(Request $request, Response $response, $args){
 
        $loginData = json_decode($request->getBody(), true);
        $password = $loginData['password'];
        $netbankId = $loginData['netbankId'];
        // validáció
        $user = Users::where('netbank_id', $netbankId)->first(); // first elhagyható elvileg
    
        if ($user === NULL){
            $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
            $response->getBody()->write($kimenet);
            return $response
            ->withHeader ('Content-type', 'application/ son')
            ->withStatus(404);
        }
        
        if (!password_verify($password, $user->password)) {
            throw new Exception('Hibás id vagy jelszó');
        }
        $token = new Token();
        $token->user_id = $user->id;
        $token->token = bin2hex(random_bytes(64));
        /* 
        check, hogy a token nem létezik a db-ben, 
        pl. lehetne unique stb.
        */
        $token->save();

        $kimenet = json_encode($user); // vagy: $kimenet = $currencies->toJson();
        /*$response->getBody()->write($kimenet);
        return $response
            ->withHeader ('Content-type', 'application/json')
            ->withStatus(200);*/
            
            $response->getBody()->write(json_encode([
                "id" => $user->id,
                "firstname" => $user->firstname,
                "lastname" => $user->lastname,
                "netbank_id" => $user->netbank_id,
                //"pincode" => $user->pincode, // ez elvileg nem kell csak a mobil apphoz
                //"password" => $user->password, // értelmetlen ezt visszaadni, teszt jelleggel hagytam benne
                "created_on" => $user->created_on,
                "last_login" => $user->last_login,
                "token" => $token->token,
            ]));
            $user->update(['last_login' => date('Y-m-d H:i:s')]); //ez is jó az előző helyett!
            
            return $response
            ->withHeader ( 'Content-Type', 'application/json')
            ->withStatus(200);
    });

    $app->group("/api", function(RouteCollectorProxy $group){
     
        $group->get('/getcurrencies', function (Request $request, Response $response, $args){
            $currencies = Currencies::all();
            $kimenet = json_encode($currencies); // vagy: $kimenet = $currencies->toJson();
            $response->getBody()->write($kimenet);
            return $response
                ->withHeader ('Content-type', 'application/json')
                ->withStatus(200);
        });

        $group->get('/getforeigncurrencies', function (Request $request, Response $response, $args){
            $currencies = ForeignCurrencies::all();
            $kimenet = json_encode($currencies); // vagy: $kimenet = $currencies->toJson();
            $response->getBody()->write($kimenet);
            return $response
                ->withHeader ('Content-type', 'application/json')
                ->withStatus(200);
        });

        $group->get('/getbranches', function (Request $request, Response $response, $args){
            $branches = Branches::all();
            $kimenet = json_encode($branches); // vagy: $kimenet = $currencies->toJson();
            $response->getBody()->write($kimenet);
            return $response
                ->withHeader ('Content-type', 'application/json')
                ->withStatus(200);
        });

        $group->get('/getatms', function (Request $request, Response $response, $args){
            $branches = Atms::all();
            $kimenet = json_encode($branches); // vagy: $kimenet = $currencies->toJson();
            $response->getBody()->write($kimenet);
            return $response
                ->withHeader ('Content-type', 'application/json')
                ->withStatus(200);
        });
    
    });

    $app->group("/api", function(RouteCollectorProxy $group){
     
        $group->get('/hello2', function(Request $request, Response $response, $args){
            $response->getBody()->write(json_encode([
                'Hello' => 'World',
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                    ->withStatus(200);
        });
    
        $group->put('/changepassword/{netbank_id}', function (Request $request, Response $response, $args){
            if (!is_numeric($args['netbank_id']) || $args['netbank_id'] <= 0) {
                $kimenet = json_encode(['error' => RESPONSE_MESSAGE_ID_LESS_THAN_NULL]);
                $response->getBody()->write($kimenet);
                return $response
                    ->withHeader ('Content-type', 'application/json')
                    ->withStatus(400);
            }
            $data = $request->getParsedBody(); // kell hozzá ez a megfelelő helyre: $app->addBodyParsingMiddleware();
            isTheseParametersAvailable(array('password'), $data);
            $user = Users::where('netbank_id', $args['netbank_id'])->first(); // first elhagyható elvileg
            if ($user === NULL){
                $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
                $response->getBody()->write($kimenet);
                return $response
                ->withHeader ('Content-type', 'application/json')
                ->withStatus(404);
            }
            $data = json_decode($request->getBody(), true);
            $user = Users::where('netbank_id', $args['netbank_id'])->first() // first elhagyható elvileg
                //->save();
                ->update(['password' => password_hash($data['password'], PASSWORD_DEFAULT)]);
            $response->getBody()->write(json_encode($user));
            return $response
                ->withHeader ('Content-type', 'application/json')
                ->withStatus(204);
        });     

        $group->post('/getstatements', function (Request $request, Response $response, $args){
            // átírni az error üzeneteket
            $data = json_decode($request->getBody(), true);
            /*if (!is_numeric($data['id_user']) || $args['id_user'] <= 0) {
                $kimenet = json_encode(['error' => RESPONSE_MESSAGE_ID_LESS_THAN_NULL]);
                $response->getBody()->write($kimenet);
                return $response
                ->withHeader ('Content-type', 'application/json')
                ->withStatus(400);
            }*/
            $statements = Statements::where('id_user', '=', $data['id_user'])
                //->take(1) // csak 1 rekordot ad vissza. Elvileg nem lehet több találat, csak hiba vagy tesztelés alatt (ha olyanok a teszt adatok)
                ->get();
            if ($statements === NULL){
                $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
                $response->getBody()->write($kimenet);
                return $response
                ->withHeader ('Content-type', 'application/ son')
                ->withStatus(404);
            }
            $response->getBody()->write(json_encode($statements));
            return $response
                ->withHeader ('Content-type', 'application/json')
                ->withStatus(200);
        });

        $group->post('/getbeneficiaries', function (Request $request, Response $response, $args){
            // átírni az error üzeneteket
            $data = json_decode($request->getBody(), true);
            /*if (!is_numeric($data['id_user']) || $args['id_user'] <= 0) {
                $kimenet = json_encode(['error' => RESPONSE_MESSAGE_ID_LESS_THAN_NULL]);
                $response->getBody()->write($kimenet);
                return $response
                ->withHeader ('Content-type', 'application/json')
                ->withStatus(400);
            }*/
            $beneficiaries = Beneficiaries::where('id_user', '=', $data['id_user'])
                //->take(1) // csak 1 rekordot ad vissza. Elvileg nem lehet több találat, csak hiba vagy tesztelés alatt (ha olyanok a teszt adatok)
                ->get();
            if ($beneficiaries === NULL){
                $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
                $response->getBody()->write($kimenet);
                return $response
                ->withHeader ('Content-type', 'application/ son')
                ->withStatus(404);
            }
            $response->getBody()->write(json_encode($beneficiaries));
            return $response
                ->withHeader ('Content-type', 'application/json')
                ->withStatus(200);
        });

        $group->post('/getsavings', function (Request $request, Response $response, $args){
            // átírni az error üzeneteket
            $data = json_decode($request->getBody(), true);
            /*if (!is_numeric($data['id_user']) || $args['id_user'] <= 0) {
                $kimenet = json_encode(['error' => RESPONSE_MESSAGE_ID_LESS_THAN_NULL]);
                $response->getBody()->write($kimenet);
                return $response
                ->withHeader ('Content-type', 'application/json')
                ->withStatus(400);
            }*/
            //$savings = Savings::where('id_user', '=', $data['id_user'])
            
            /*ez működik, de nem tudok mellé where-t rakni 
            $savings = Savings::leftjoin('saving_types', 'savings.id_type', '=', 'saving_types.id')
                ->leftJoin('bank_accounts', 'savings.id_bank_account', '=', 'bank_accounts.id')
                ->get();*/
        /*
            $savings = Savings::where('id_user', '=', $data['id_user'])
            ->leftJoin('saving_types', 'savings.id_type', '=', 'saving_types.id')
            ->where('status', '=', 'Expired')
            ->where('id_type', '=', '5')
            ->where('amount', '>', '1')
            ->where('amount', '<', '1111');
        */
            //ez logikailag jó, de nem működik, csak ha kiszedem a második join-t. HA az elsőt szedem ki, akkor sem mműködik
            $savings = Savings::where('savings.id_user', '=', $data['id_user'])
                ->where('savings.status', '=', 'active')
                ->leftJoin('saving_types', 'savings.id_type', '=', 'saving_types.id')
                ->leftJoin('bank_accounts', 'savings.id_bank_account', '=', 'bank_accounts.id')
                ->select('saving_types.type', 'savings.expire_date', 'saving_types.rate', 'savings.amount', 'bank_accounts.currency')
                ->get();

        /*   
            $savings = Savings::leftJoin('saving_types', 'savings.id_type', '=', 'saving_types.id')
            ->leftJoin('bank_accounts', 'savings.id_bank_account', '=', 'bank_accounts.id')
            ->where('status', '=', 'active')
            ->get();*/

                //->where('status', '=', 'active'))
                //->take(1) // csak 1 rekordot ad vissza. Elvileg nem lehet több találat, csak hiba vagy tesztelés alatt (ha olyanok a teszt adatok)
         /*   if ($savings === NULL){
                $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
                $response->getBody()->write($kimenet);
                return $response
                ->withHeader ('Content-type', 'application/ son')
                ->withStatus(404);
            }*/

            $response->getBody()->write(json_encode($savings));
            return $response
                ->withHeader ('Content-type', 'application/json')
                ->withStatus(200);
        });

        $group->post('/getaccountbalances', function (Request $request, Response $response, $args){
            // átírni az error üzeneteket
            $data = json_decode($request->getBody(), true);
            /*if (!is_numeric($data['id_user']) || $args['id_user'] <= 0) {
                $kimenet = json_encode(['error' => RESPONSE_MESSAGE_ID_LESS_THAN_NULL]);
                $response->getBody()->write($kimenet);
                return $response
                ->withHeader ('Content-type', 'application/json')
                ->withStatus(400);
            }*/
            $bankaccounts = BankAccounts::where('id_user', '=', $data['id_user'])
                //->take(1) // csak 1 rekordot ad vissza. Elvileg nem lehet több találat, csak hiba vagy tesztelés alatt (ha olyanok a teszt adatok)
                ->get();
            if ($bankaccounts === NULL){
                $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
                $response->getBody()->write($kimenet);
                return $response
                ->withHeader ('Content-type', 'application/ son')
                ->withStatus(404);
            }
            $response->getBody()->write(json_encode($bankaccounts));
            return $response
                ->withHeader ('Content-type', 'application/json')
                ->withStatus(200);
        });

        $group->post('/api/createtransferonetime', function (Request $request, Response $response, $args){
            //$data = json_decode($request->getBody(), true); // ehelyett van az alatta levő sor
            $data = $request->getParsedBody(); // kell hozzá ez a megfelelő helyre: $app->addBodyParsingMiddleware();
            isTheseParametersAvailable(array('id_user', 'id_bank_account_number', 'currency', 'amoueeent', 'partner_name', 'partner_account_number'), $data); // a comment mezőtkiszedtem innen , mert annak nem kötelező lennie

            //
            //  EGYENLEG LEKÉRÉSE ÉS MEGVIZSGÁLÁSA HOGY VAN E ELÉG PÉNZ AZ UTALÁSHOZ
            //

            $transaction = Transactions::create($data);
            $transaction->save();
            $response->getBody()->write(json_encode($transaction));
            return $response
                ->withHeader ('Content-type', 'application/json')
                ->withStatus(201);
        });

    })->add(new AuthMiddleware());

};