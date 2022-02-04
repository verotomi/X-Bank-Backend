<?php
	use Psr\Http\Message\ResponseInterface as Response;
	use Psr\Http\Message\ServerRequestInterface as Request;
	//use Slim\Factory\AppFactory;
    //use Illuminate\Database\Capsule\Manager;
    use Models\RecurringTransfers;

$app->get('/api/recurring_transfers', function (Request $request, Response $response, $args){
    $recurring_transfers = RecurringTransfers::all();
    $kimenet = json_encode($recurring_transfers); // vagy: $kimenet = $recurring_transfers->toJson();
    $response->getBody()->write($kimenet);
    return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(200);
});

$app->get('/api/recurring_transfers/{id}', function (Request $request, Response $response, $args){
    if (!is_numeric($args['id']) || $args['id'] <= 0) {
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_ID_LESS_THAN_NULL]);
        $response->getBody()->write($kimenet);
        return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(400);
    }
    $recurring_transfers = RecurringTransfers::find($args['id']);
    if ($recurring_transfers === NULL){
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
        $response->getBody()->write($kimenet);
        return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(404);
    }
    $response->getBody()->write(json_encode($recurring_transfers));
    return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(200);
});

$app->post('/api/recurring_transfers', function (Request $request, Response $response, $args){
    $data = json_decode($request->getBody(), true);
    // ide majd kell validálás! 
    $recurring_transfers = RecurringTransfers::create($data);
    $recurring_transfers->save(); // ez mit csinál? Enélkül is működik!
    $response->getBody()->write(json_encode($recurring_transfers));
    return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(201);
});

$app->put('/api/recurring_transfers/{id}', function (Request $request, Response $response, $args){
    if (!is_numeric($args['id']) || $args['id'] <= 0) {
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_ID_LESS_THAN_NULL]);
        $response->getBody()->write($kimenet);
        return $response
            ->withHeader ('Content-type', 'application/json')
            ->withStatus(400);
    }
    $recurring_transfers = RecurringTransfers::find($args['id']);
    if ($recurring_transfers === NULL){
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
        $response->getBody()->write($kimenet);
        return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(404);
    }
    $data = json_decode($request->getBody(), true);
    $recurring_transfers->fill($data);
    $recurring_transfers->save();
    $response->getBody()->write(json_encode($recurring_transfers));
    return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(204);
});

$app->delete('/api/recurring_transfers/{id}', function (Request $request, Response $response, $args){
    if (!is_numeric($args['id']) || $args['id'] <= 0) {
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_ID_LESS_THAN_NULL]);
        $response->getBody()->write($kimenet);
        return $response
            ->withHeader ('Content-type', 'application/json')
            ->withStatus(400);
    }
    $recurring_transfers = RecurringTransfers::find($args['id']);
    if ($recurring_transfers === NULL){
        $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
        $response->getBody()->write($kimenet);
        return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(404);
    }
    $recurring_transfers->delete();
    return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(204);
});
?>
