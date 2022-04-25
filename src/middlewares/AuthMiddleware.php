<?php

namespace Middlewares;

use Exception;
use Models\Token;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Tokenes bejelentkezés megvalósítása.
 */
class AuthMiddleware
{
  public function __invoke(Request $request, RequestHandler $handler): Response
  {
    $auth = $request->getHeader('Authorization');
    if (count($auth) !== 1) {
      throw new Exception('Hibás Authorization header');
    }
    $authArr = mb_split(' ', $auth[0]);
    if ($authArr[0] !== 'Bearer') {
      throw new Exception("Nem támogatott autentikációs módszer");
    }
    $tokenStr = $authArr[1];
    $token = "";
    try {
      $token = Token::where('token', $tokenStr)->firstOrFail();
      $created = strtotime($token["created_at"]->format('Y-m-d H:i:s'));
      $currenttime = time();
      $request->getParsedBody();
      if ($currenttime > $created + TOKEN_LIFETIME) {
        $response = new \Slim\Psr7\Response;
        $kimenet = json_encode(['error' => 'Lejárt token!']);
        $response->getBody()->write($kimenet);
        return $response;
      } else {
        return $handler->handle($request);
      }
    } catch (\Throwable $th) {
      $response = new \Slim\Psr7\Response;
      $response->getBody()->write('Nem létező token!');
      return $response;
    }
  }
}
