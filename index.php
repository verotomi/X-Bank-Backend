<?php

use Slim\Factory\AppFactory;
use Illuminate\Database\Capsule\Manager;

require __DIR__ . '/vendor/autoload.php';
include_once __DIR__ . '/src/constants.php';
$config = require __DIR__ . '/src/config.php';

$dbManager = new Manager();
$dbManager->addConnection([
  'driver' => $config['DB_DRIVER'],
  'host' => $config['DB_HOST'],
  'database' => $config['DB_DATABASE'],
  'username' => $config['DB_USER'],
  'password' => $config['DB_PASS'],
  'charset' => $config['DB_CHARSET'],
  'collation' => $config['DB_COLLATION'],
  'prefix' => $config['DB_PREFIX'],
]);
$dbManager->setAsGlobal();
$dbManager->bootEloquent();
$app = AppFactory::create();
$app->setBasePath((function () {
  $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
  $uri = (string) parse_url('http://a' . $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
  if (stripos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
    return $_SERVER['SCRIPT_NAME'];
  }
  if ($scriptDir !== '/' && stripos($uri, $scriptDir) === 0) {
    return $scriptDir;
  }
  return '';
})());
$app->addBodyParsingMiddleware();
$routes = require 'src/routes.php';
$routes($app);
$app->run();
