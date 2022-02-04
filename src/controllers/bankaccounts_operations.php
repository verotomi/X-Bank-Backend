<?php
	use Psr\Http\Message\ResponseInterface as Response;
	use Psr\Http\Message\ServerRequestInterface as Request;
    use Models\BankAccounts;

//use Slim\Factory\AppFactory;
    //use Illuminate\Database\Capsule\Manager;

$app->get('/api/bank_accounts', function (Request $request, Response $response, $args){
    $bank_accounts = BankAccounts::all();
    $kimenet = json_encode($bank_accounts); // vagy: $kimenet = $bank_accounts->toJson();
    $response->getBody()->write($kimenet);
    return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(200);
});

/*$app->get('/api/bank_accounts_new', function (Request $request, Response $response, $args){
    $bank_accounts = BankAccounts::find(1)->user;
    $kimenet = json_encode($bank_accounts); // vagy: $kimenet = $bank_accounts->toJson();
    $response->getBody()->write($kimenet);
    return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(200);
});*/

$app->get('/api/bank_accounts_new2', function (Request $request, Response $response, $args){
    $bank_accounts = BankAccounts::where('currency', '=', 'Forint')
        ->leftJoin('users', 'bank_accounts.id_user', '=', 'users.id')
        ->get();
    $kimenet = json_encode($bank_accounts); // vagy: $kimenet = $bank_accounts->toJson();
    $response->getBody()->write($kimenet);
    return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(200);
});

$app->get('/api/bank_accounts/{id}', function (Request $request, Response $response, $args){
    if (!is_numeric($args['id']) || $args['id'] <= 0) {
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_ID_LESS_THAN_NULL]);
        $response->getBody()->write($kimenet);
        return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(400);
    }
    $bank_accounts = BankAccounts::find($args['id']);
    if ($bank_accounts === NULL){
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
        $response->getBody()->write($kimenet);
        return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(404);
    }
    $response->getBody()->write(json_encode($bank_accounts));
    return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(200);
});

$app->post('/api/bank_accounts', function (Request $request, Response $response, $args){
    $data = json_decode($request->getBody(), true);
    // ide majd kell validálás! 
    $bank_accounts = BankAccounts::create($data);
    $bank_accounts->save(); // ez mit csinál? Enélkül is működik!
    $response->getBody()->write(json_encode($bank_accounts));
    return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(201);
});

$app->put('/api/bank_accounts/{id}', function (Request $request, Response $response, $args){
    if (!is_numeric($args['id']) || $args['id'] <= 0) {
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_ID_LESS_THAN_NULL]);
        $response->getBody()->write($kimenet);
        return $response
            ->withHeader ('Content-type', 'application/json')
            ->withStatus(400);
    }
    $bank_accounts = BankAccounts::find($args['id']);
    if ($bank_accounts === NULL){
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
        $response->getBody()->write($kimenet);
        return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(404);
    }
    $data = json_decode($request->getBody(), true);
    $bank_accounts->fill($data);
    $bank_accounts->save();
    $response->getBody()->write(json_encode($bank_accounts));
    return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(204);
});

$app->delete('/api/bank_accounts/{id}', function (Request $request, Response $response, $args){
    if (!is_numeric($args['id']) || $args['id'] <= 0) {
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_ID_LESS_THAN_NULL]);
        $response->getBody()->write($kimenet);
        return $response
            ->withHeader ('Content-type', 'application/json')
            ->withStatus(400);
    }
    $bank_accounts = BankAccounts::find($args['id']);
    if ($bank_accounts === NULL){
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
        $response->getBody()->write($kimenet);
        return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(404);
    }
    $bank_accounts->delete();
    return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(204);
});
?>
