<?php
	use Psr\Http\Message\ResponseInterface as Response;
	use Psr\Http\Message\ServerRequestInterface as Request;
	//use Slim\Factory\AppFactory;
    //use Illuminate\Database\Capsule\Manager;
    use Models\Beneficiaries;

$app->get('/api/beneficiaries', function (Request $request, Response $response, $args){
    $beneficiaries = Beneficiaries::all();
    $kimenet = json_encode($beneficiaries); // vagy: $kimenet = $beneficiaries->toJson();
    $response->getBody()->write($kimenet);
    return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(200);
});

$app->get('/api/beneficiaries/{id}', function (Request $request, Response $response, $args){
    if (!is_numeric($args['id']) || $args['id'] <= 0) {
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_ID_LESS_THAN_NULL]);
        $response->getBody()->write($kimenet);
        return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(400);
    }
    $beneficiaries = Beneficiaries::find($args['id']);
    if ($beneficiaries === NULL){
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
        $response->getBody()->write($kimenet);
        return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(404);
    }
    $response->getBody()->write(json_encode($beneficiaries));
    return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(200);
});

$app->post('/api/beneficiaries', function (Request $request, Response $response, $args){
    $data = json_decode($request->getBody(), true);
    // ide majd kell validálás! 
    $beneficiaries = Beneficiaries::create($data);
    $beneficiaries->save(); // ez mit csinál? Enélkül is működik!
    $response->getBody()->write(json_encode($beneficiaries));
    return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(201);
});

$app->put('/api/beneficiaries/{id}', function (Request $request, Response $response, $args){
    if (!is_numeric($args['id']) || $args['id'] <= 0) {
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_ID_LESS_THAN_NULL]);
        $response->getBody()->write($kimenet);
        return $response
            ->withHeader ('Content-type', 'application/json')
            ->withStatus(400);
    }
    $beneficiaries = Beneficiaries::find($args['id']);
    if ($beneficiaries === NULL){
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
        $response->getBody()->write($kimenet);
        return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(404);
    }
    $data = json_decode($request->getBody(), true);
    $beneficiaries->fill($data);
    $beneficiaries->save();
    $response->getBody()->write(json_encode($beneficiaries));
    return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(204);
});

$app->delete('/api/beneficiaries/{id}', function (Request $request, Response $response, $args){
    if (!is_numeric($args['id']) || $args['id'] <= 0) {
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_ID_LESS_THAN_NULL]);
        $response->getBody()->write($kimenet);
        return $response
            ->withHeader ('Content-type', 'application/json')
            ->withStatus(400);
    }
    $beneficiaries = Beneficiaries::find($args['id']);
    if ($beneficiaries === NULL){
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
        $response->getBody()->write($kimenet);
        return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(404);
    }
    $beneficiaries->delete();
    return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(204);
});
?>
