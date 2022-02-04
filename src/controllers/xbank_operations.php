<?php
	use Psr\Http\Message\ResponseInterface as Response;
	use Psr\Http\Message\ServerRequestInterface as Request;
	//use Slim\Factory\AppFactory;
    //use Illuminate\Database\Capsule\Manager;
    use Models\Currencies;
    use Models\Foreigncurrencies;
    use Models\Users;
    use Models\Bankaccounts;
    use Models\Creditcards;
    use Models\Transactions;
    use Models\Savings;
    use Middlewares\AuthMiddleware;
    use Action\PreflightAction;




    function isTheseParametersAvailable_old($params, $data){
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

    $app->get('/api/getcurrencies_old', function (Request $request, Response $response, $args){
        $currencies = Currencies::all();
        $kimenet = json_encode($currencies); // vagy: $kimenet = $currencies->toJson();
        $response->getBody()->write($kimenet);
        return $response
            ->withHeader ('Content-type', 'application/json')
            ->withStatus(200);
    });

    $app->get('/api/getforeigncurrencies_old', function (Request $request, Response $response, $args){
        $currencies = Foreigncurrencies::all();
        $kimenet = json_encode($currencies); // vagy: $kimenet = $currencies->toJson();
        $response->getBody()->write($kimenet);
        return $response
            ->withHeader ('Content-type', 'application/json')
            ->withStatus(200);
    });

    $app->get('/api/trytologin_old/{mobilebank_id}/{pincode}', function (Request $request, Response $response, $args){
        // kibővíteni az ellenőrzést a pincode-ra is
        // átírni az error üzeneteket
        if (!is_numeric($args['mobilebank_id']) || $args['mobilebank_id'] <= 0) {
            $kimenet = json_encode(['error' => RESPONSE_MESSAGE_ID_LESS_THAN_NULL]);
            $response->getBody()->write($kimenet);
            return $response
            ->withHeader ('Content-type', 'application/json')
            ->withStatus(400);
        }
        $user = Users::where('mobilebank_id', '=', $args['mobilebank_id'], ' AND ', 'pincode', '=', $args['pincode'])
        //->select('id','lastname')    
        ->get();
        if ($user === NULL){
            $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
            $response->getBody()->write($kimenet);
            return $response
            ->withHeader ('Content-type', 'application/ son')
            ->withStatus(404);
        }
        $response->getBody()->write(json_encode($user));
        return $response
            ->withHeader ('Content-type', 'application/json')
            ->withStatus(200);
    });

    /**
     *  beleolvasztottam a login-ba
     */
    $app->put('/api/updatelastlogintime_old/{id}', function (Request $request, Response $response, $args){
        if (!is_numeric($args['id']) || $args['id'] <= 0) {
            $kimenet = json_encode(['error' => RESPONSE_MESSAGE_ID_LESS_THAN_NULL]);
            $response->getBody()->write($kimenet);
            return $response
                ->withHeader ('Content-type', 'application/json')
                ->withStatus(400);
        }
        $user = Users::find($args['id']);
        if ($user === NULL){
            $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
            $response->getBody()->write($kimenet);
            return $response
            ->withHeader ('Content-type', 'application/json')
            ->withStatus(404);
        }
        /*$users = Users::where('id', '=', $args['id'])
            ->update(['last_login' => date('Y-m-d H:i:s')]);*/
        $user = Users::where('id', '=', $args['id']);
        $user->update(['last_login' => date('Y-m-d H:i:s')]); //ez is jó az előző helyett!
        $response->getBody()->write(json_encode($user));
        return $response
            ->withHeader ('Content-type', 'application/json')
            ->withStatus(204);
    });

    $app->get('/api/getaccountbalances/{id_user}', function (Request $request, Response $response, $args){
        // átírni az error üzeneteket
        var_dump($args);

        if (!is_numeric($args['id_user']) || $args['id_user'] <= 0) {
            $kimenet = json_encode(['error' => RESPONSE_MESSAGE_ID_LESS_THAN_NULL]);
            $response->getBody()->write($kimenet);
            return $response
            ->withHeader ('Content-type', 'application/json')
            ->withStatus(400);
        }
        $bankaccounts = Bankaccounts::where('id_user', '=', $args['id_user'])
            ->take(1) // csak 1 rekordot ad vissza. Elvileg nem lehet több találat, csak hiba vagy tesztelés alatt (ha olyanok a teszt adatok)
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
    })->add(new AuthMiddleware());

    $app->put('/api/changepincode_old/{id}', function (Request $request, Response $response, $args){
        if (!is_numeric($args['id']) || $args['id'] <= 0) {
            $kimenet = json_encode(['error' => RESPONSE_MESSAGE_ID_LESS_THAN_NULL]);
            $response->getBody()->write($kimenet);
            return $response
                ->withHeader ('Content-type', 'application/json')
                ->withStatus(400);
        }
        $data = $request->getParsedBody(); // kell hozzá ez a megfelelő helyre: $app->addBodyParsingMiddleware();
        isTheseParametersAvailable(array('pincode'), $data);
        $user = Users::find($args['id']);
        if ($user === NULL){
            $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
            $response->getBody()->write($kimenet);
            return $response
            ->withHeader ('Content-type', 'application/json')
            ->withStatus(404);
        }
        $data = json_decode($request->getBody(), true);
        $user = Users::where('id', '=', $args['id'])
            //->save();
            ->update(['pincode' => $data['pincode']]);
        $response->getBody()->write(json_encode($user));
        return $response
            ->withHeader ('Content-type', 'application/json')
            ->withStatus(204);
    });

    $app->get('/api/getcreditcards/{id_user}', function (Request $request, Response $response, $args){
        // átírni az error üzeneteket
        if (!is_numeric($args['id_user']) || $args['id_user'] <= 0) {
            $kimenet = json_encode(['error' => RESPONSE_MESSAGE_ID_LESS_THAN_NULL]);
            $response->getBody()->write($kimenet);
            return $response
            ->withHeader ('Content-type', 'application/json')
            ->withStatus(400);
        }
        $creditcards = Creditcards::where('id_user', '=', $args['id_user'])
            ->take(1) // csak 1 rekordot ad vissza. Elvileg nem lehet több találat, csak hiba vagy tesztelés alatt (ha olyanok a teszt adatok)
            ->get();
        if ($creditcards === NULL){
            $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
            $response->getBody()->write($kimenet);
            return $response
            ->withHeader ('Content-type', 'application/ son')
            ->withStatus(404);
        }
        $response->getBody()->write(json_encode($creditcards));
        return $response
            ->withHeader ('Content-type', 'application/json')
            ->withStatus(200);
    });

    $app->put('/api/updatecreditcard/{id}', function (Request $request, Response $response, $args){
        if (!is_numeric($args['id']) || $args['id'] <= 0) {
            $kimenet = json_encode(['error' => RESPONSE_MESSAGE_ID_LESS_THAN_NULL]);
            $response->getBody()->write($kimenet);
            return $response
                ->withHeader ('Content-type', 'application/json')
                ->withStatus(400);
        }
        $creditcard = Creditcards::find($args['id']);
        if ($creditcard === NULL){
            $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
            $response->getBody()->write($kimenet);
            return $response
            ->withHeader ('Content-type', 'application/json')
            ->withStatus(404);
        }
        $data = json_decode($request->getBody(), true);
        $creditcard = Creditcards::where('id', '=', $args['id'])
            //->save();
            ->update([
            'status' => $data['status'],
            'limit_atm' => $data['limit_atm'],
            'limit_pos' => $data['limit_pos'],
            'limit_online' => $data['limit_online']
        ]);

        $response->getBody()->write(json_encode($creditcard));
        return $response
            ->withHeader ('Content-type', 'application/json')
            ->withStatus(204);
    });

    $app->post('/api/transferonetime', function (Request $request, Response $response, $args){
        $data = json_decode($request->getBody(), true);
        // ide majd kell validálás! 
        $data['arrived_on'] = date('Y-m-d H:i:s');
        $transaction = Transactions::create($data);
        $transaction->save();
        $response->getBody()->write(json_encode($transaction));
        return $response
            ->withHeader ('Content-type', 'application/json')
            ->withStatus(201);

        // utalási költség generálása
        // bejövő utalás generálása (banki ügyfél esetén)

    });

    $app->get('/api/getrecurringtransfers', function (Request $request, Response $response, $args){    
    });

    $app->post('/api/transferrecurring', function (Request $request, Response $response, $args){    
    });

    $app->delete('/api/deleterecurringtransfer', function (Request $request, Response $response, $args){   
    });

    $app->put('/api/updaterecurringtransfer', function (Request $request, Response $response, $args){   
    });

    $app->get('/api/getbankaccounthistory', function (Request $request, Response $response, $args){    
    });

    $app->get('/api/getbankaccountstatements', function (Request $request, Response $response, $args){   
    });

    $app->get('/api/savings', function (Request $request, Response $response, $args){
        $savings = Savings::all();
        $kimenet = json_encode($savings); // vagy: $kimenet = $savings->toJson();
        $response->getBody()->write($kimenet);
        return $response
            ->withHeader ('Content-type', 'application/json')
            ->withStatus(200);
    });

    $app->put('/api/breakdeposit', function (Request $request, Response $response, $args){  
    });

    $app->post('/api/insertsaving', function (Request $request, Response $response, $args){  
    });

    $app->get('/api/getsavingtypes', function (Request $request, Response $response, $args){  
    });

    $app->get('/api/getbeneficiaries', function (Request $request, Response $response, $args){ 
    });

    $app->delete('/api/deletebeneficiary', function (Request $request, Response $response, $args){
    });

    $app->post('/api/insertbeneficiary', function (Request $request, Response $response, $args){
    });

    $app->put('/api/updatebeneficiary', function (Request $request, Response $response, $args){
    });


?>
