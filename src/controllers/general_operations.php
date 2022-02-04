<?php
	use Psr\Http\Message\ResponseInterface as Response;
	use Psr\Http\Message\ServerRequestInterface as Request;



/*$app->get('/api', function (Request $request, Response $response, $args){
    $kimenet = json_encode(['error'=> RESPONSE_MESSAGE_NOT_AUTHORIZED]);
    $response->getBody()->write($kimenet);
    return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(401);
});

$app->get('/api/', function (Request $request, Response $response, $args){
    $kimenet = json_encode(['error'=> RESPONSE_MESSAGE_NOT_AUTHORIZED]);
    $response->getBody()->write($kimenet);
    return $response
        ->withHeader ('Content-type', 'application/json')
        ->withStatus(401);
});*/
?>