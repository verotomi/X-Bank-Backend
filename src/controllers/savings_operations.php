<?php
	use Psr\Http\Message\ResponseInterface as Response;
	use Psr\Http\Message\ServerRequestInterface as Request;
	//use Slim\Factory\AppFactory;
    //use Illuminate\Database\Capsule\Manager;
    use Models\Savings;

$app->get('/api/_savings', function (Request $request, Response $response, $args){
    $savings = Savings::all();
    $kimenet = json_encode($savings); // vagy: $kimenet = $savings->toJson();
    $response->getBody()->write($kimenet);
    return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(200);
});

$app->get('/api/savings/{id}', function (Request $request, Response $response, $args){
    if (!is_numeric($args['id']) || $args['id'] <= 0) {
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_ID_LESS_THAN_NULL]);
        $response->getBody()->write($kimenet);
        return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(400);
    }
    $savings = Savings::find($args['id']);
    if ($savings === NULL){
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
        $response->getBody()->write($kimenet);
        return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(404);
    }
    $response->getBody()->write(json_encode($savings));
    return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(200);
});

$app->post('/api/savings', function (Request $request, Response $response, $args){
    $data = json_decode($request->getBody(), true);
    // ide majd kell validálás! 
    $savings = Savings::create($data);
    $savings->save(); // ez mit csinál? Enélkül is működik!
    $response->getBody()->write(json_encode($savings));
    return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(201);
});

$app->put('/api/savings/{id}', function (Request $request, Response $response, $args){
    if (!is_numeric($args['id']) || $args['id'] <= 0) {
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_ID_LESS_THAN_NULL]);
        $response->getBody()->write($kimenet);
        return $response
            ->withHeader ('Content-type', 'application/json')
            ->withStatus(400);
    }
    $savings = Savings::find($args['id']);
    if ($savings === NULL){
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
        $response->getBody()->write($kimenet);
        return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(404);
    }
    $data = json_decode($request->getBody(), true);
    $savings->fill($data);
    $savings->save();
    $response->getBody()->write(json_encode($savings));
    return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(204);
});

$app->delete('/api/savings/{id}', function (Request $request, Response $response, $args){
    if (!is_numeric($args['id']) || $args['id'] <= 0) {
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_ID_LESS_THAN_NULL]);
        $response->getBody()->write($kimenet);
        return $response
            ->withHeader ('Content-type', 'application/json')
            ->withStatus(400);
    }
    $savings = Savings::find($args['id']);
    if ($savings === NULL){
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
        $response->getBody()->write($kimenet);
        return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(404);
    }
    $savings->delete();
    return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(204);
});
?>
