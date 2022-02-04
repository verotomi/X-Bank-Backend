<?php
	use Psr\Http\Message\ResponseInterface as Response;
	use Psr\Http\Message\ServerRequestInterface as Request;
    use Models\Currencies;
    use Models\Foreigncurrencies;
    use Models\Users;
    use Models\Bankaccounts;
    use Models\Creditcards;
    use Models\Transactions;

    /**
     * Itt vannak azok a függvények, amik valamiért elkészültek, da az app nem használja őket
     */

    function isTheseParametersAvailable($params, $data){
        $available = true; 
        $missingparams = ""; 
        foreach($params as $param){
            if (!array_key_exists($param, $data)){
                $available = false; 
                $missingparams = $missingparams . ", " . $param;
            }
        }
        if(!$available){
            $response = array(); 
            $response['error'] = true; 
            $response['message'] = 'Parameters' . substr($missingparams, 1, strlen($missingparams)) . ' missing';
            echo json_encode($response);
            die();
        }
    }

    $app->post('/api/createcurrency', function (Request $request, Response $response, $args){
        //$data = json_decode($request->getBody(), true); // ehelyett van az alatta levő sor
        $data = $request->getParsedBody(); // kell hozzá ez a megfelelő helyre: $app->addBodyParsingMiddleware();
        isTheseParametersAvailable(array('name','buy','sell','validfrom'), $data);
        $currency = Currencies::create($data);
        $currency->save();
        $response->getBody()->write(json_encode($currency));
        return $response
            ->withHeader ('Content-type', 'application/json')
            ->withStatus(201);
    });
    
    $app->get('/api/getbothcurrencies', function (Request $request, Response $response, $args){    
        $currencies = Currencies::all();
        $foreigncurrencies = Foreigncurrencies::all();
        $kimenet = json_encode(array_merge(json_decode($currencies, true), json_decode($foreigncurrencies, true)));
        $response->getBody()->write($kimenet);
        return $response
            ->withHeader ('Content-type', 'application/json')
            ->withHeader('Content', 'Both Currencies')
            ->withStatus(200);
    });

    $app->put('/api/updatecurrency/{id}', function (Request $request, Response $response, $args){
        if (!is_numeric($args['id']) || $args['id'] <= 0) {
            $kimenet = json_encode(['error' => RESPONSE_MESSAGE_ID_LESS_THAN_NULL]);
            $response->getBody()->write($kimenet);
            return $response
                ->withHeader ('Content-type', 'application/json')
                ->withStatus(400);
        }
        $currency = Currencies::find($args['id']);
        if ($currency === NULL){
            $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
            $response->getBody()->write($kimenet);
            return $response
            ->withHeader ('Content-type', 'application/json')
            ->withStatus(404);
        }
        //átírni úgy, hogy lekérje az id alapján az adatokat, és azokat használja, ne a body-t
        $data = json_decode($request->getBody(), true);
        $currency->fill($data);
        $currency->save();
        $response->getBody()->write(json_encode($currency));
        return $response
            ->withHeader ('Content-type', 'application/json')
            ->withStatus(204);
    });

    $app->delete('/api/deletecurrency/{id}', function (Request $request, Response $response, $args){
        if (!is_numeric($args['id']) || $args['id'] <= 0) {
            $kimenet = json_encode(['error' => RESPONSE_MESSAGE_ID_LESS_THAN_NULL]);
            $response->getBody()->write($kimenet);
            return $response
                ->withHeader ('Content-type', 'application/json')
                ->withStatus(400);
        }
        $currency = Currencies::find($args['id']);
        if ($currency === NULL){
            $kimenet = json_encode(['error' => RESPONSE_MESSAGE_NO_DATA]);
            $response->getBody()->write($kimenet);
            return $response
            ->withHeader ('Content-type', 'application/json')
            ->withStatus(404);
        }
        $currency->delete();
        return $response
            ->withHeader ('Content-type', 'application/json')
            ->withStatus(204);
    });

?>
