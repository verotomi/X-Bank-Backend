<?php
	use Psr\Http\Message\ResponseInterface as Response;
	use Psr\Http\Message\ServerRequestInterface as Request;
	//use Slim\Factory\AppFactory;
    //use Illuminate\Database\Capsule\Manager;
    use Models\ForeignCurrencies;

$app->get('/api/foreigncurrencies', function (Request $request, Response $response, $args){
    $foreign_currencies = ForeignCurrencies::all();
    $kimenet = json_encode($foreign_currencies); // vagy: $kimenet = $foreign_currencies->toJson();
    $response->getBody()->write($kimenet);
    return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(200);
});

$app->get('/api/foreigncurrencies/{id}', function (Request $request, Response $response, $args){
    if (!is_numeric($args['id']) || $args['id'] <= 0) {
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_ID_LESS_THAN_NULL]);
        $response->getBody()->write($kimenet);
        return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(400);
    }
    $foreign_currencies = ForeignCurrencies::find($args['id']);
    if ($foreign_currencies === NULL){
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
        $response->getBody()->write($kimenet);
        return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(404);
    }
    $response->getBody()->write(json_encode($foreign_currencies));
    return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(200);
});

$app->post('/api/foreigncurrencies', function (Request $request, Response $response, $args){
    $data = json_decode($request->getBody(), true);
    // ide majd kell validálás! 
    $foreign_currencies = ForeignCurrencies::create($data);
    $foreign_currencies->save(); // ez mit csinál? Enélkül is működik!
    $response->getBody()->write(json_encode($foreign_currencies));
    return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(201);
});

$app->put('/api/foreigncurrencies/{id}', function (Request $request, Response $response, $args){
    if (!is_numeric($args['id']) || $args['id'] <= 0) {
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_ID_LESS_THAN_NULL]);
        $response->getBody()->write($kimenet);
        return $response
            ->withHeader ('Content-type', 'application/json')
            ->withStatus(400);
    }
    $foreign_currencies = ForeignCurrencies::find($args['id']);
    if ($foreign_currencies === NULL){
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
        $response->getBody()->write($kimenet);
        return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(404);
    }
    $data = json_decode($request->getBody(), true);
    $foreign_currencies->fill($data);
    $foreign_currencies->save();
    $response->getBody()->write(json_encode($foreign_currencies));
    return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(204);
});

$app->delete('/api/foreigncurrencies/{id}', function (Request $request, Response $response, $args){
    if (!is_numeric($args['id']) || $args['id'] <= 0) {
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_ID_LESS_THAN_NULL]);
        $response->getBody()->write($kimenet);
        return $response
            ->withHeader ('Content-type', 'application/json')
            ->withStatus(400);
    }
    $foreign_currencies = ForeignCurrencies::find($args['id']);
    if ($foreign_currencies === NULL){
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
        $response->getBody()->write($kimenet);
        return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(404);
    }
    $foreign_currencies->delete();
    return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(204);
});
?>
