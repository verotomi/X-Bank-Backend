<?php
	use Psr\Http\Message\ResponseInterface as Response;
	use Psr\Http\Message\ServerRequestInterface as Request;
	//use Slim\Factory\AppFactory;
    //use Illuminate\Database\Capsule\Manager;
    use Models\Currencies;

$app->get('/api/currencies', function (Request $request, Response $response, $args){
    $currencies = Currencies::all();
    $kimenet = json_encode($currencies); // vagy: $kimenet = $currencies->toJson();
    $response->getBody()->write($kimenet);
    return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(200);
});

$app->get('/api/currencies/{id}', function (Request $request, Response $response, $args){
    if (!is_numeric($args['id']) || $args['id'] <= 0) {
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_ID_LESS_THAN_NULL]);
        $response->getBody()->write($kimenet);
        return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(400);
    }
    $currencies = Currencies::find($args['id']);
    if ($currencies === NULL){
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
        $response->getBody()->write($kimenet);
        return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(404);
    }
    $response->getBody()->write(json_encode($currencies));
    return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(200);
});

$app->post('/api/currencies', function (Request $request, Response $response, $args){
    $data = json_decode($request->getBody(), true);
    // ide majd kell validálás! 
    $currencies = Currencies::create($data);
    $currencies->save(); // ez mit csinál? Enélkül is működik!
    $response->getBody()->write(json_encode($currencies));
    return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(201);
});

$app->put('/api/currencies/{id}', function (Request $request, Response $response, $args){
    if (!is_numeric($args['id']) || $args['id'] <= 0) {
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_ID_LESS_THAN_NULL]);
        $response->getBody()->write($kimenet);
        return $response
            ->withHeader ('Content-type', 'application/json')
            ->withStatus(400);
    }
    $currencies = Currencies::find($args['id']);
    if ($currencies === NULL){
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
        $response->getBody()->write($kimenet);
        return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(404);
    }
    $data = json_decode($request->getBody(), true);
    $currencies->fill($data);
    $currencies->save();
    $response->getBody()->write(json_encode($currencies));
    return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(204);
});

$app->delete('/api/currencies/{id}', function (Request $request, Response $response, $args){
    if (!is_numeric($args['id']) || $args['id'] <= 0) {
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_ID_LESS_THAN_NULL]);
        $response->getBody()->write($kimenet);
        return $response
            ->withHeader ('Content-type', 'application/json')
            ->withStatus(400);
    }
    $currencies = Currencies::find($args['id']);
    if ($currencies === NULL){
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
        $response->getBody()->write($kimenet);
        return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(404);
    }
    $currencies->delete();
    return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(204);
});
?>
