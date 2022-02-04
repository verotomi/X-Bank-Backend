<?php
    //use Psr\Http\Message\ResponseInterface as Response;
    //use Psr\Http\Message\ServerRequestInterface as Request;
	use Slim\Factory\AppFactory;
    use Illuminate\Database\Capsule\Manager;



	require __DIR__ . '/vendor/autoload.php';

	$config = require __DIR__ . '../src/config.php';

    //$config = require __DIR__ . '../src/constants.php';
    //include_once dirname(__FILE__) . '/constants.php';
    include_once __DIR__ . '../src/constants.php';

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
    /*$dbManager->addConnection([
    'driver' => 'mysql',
    'host' => DB_HOST,
    'database' => DB_DATABASE,
    'username' => DB_USER,
    'password' => DB_PASS,
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => ''
    ]);*/
    
    $app = AppFactory::create();
    $app->setBasePath("/15WL/X-Bank/_Backend/X-Bank");
    $app->addBodyParsingMiddleware(); // Ehhez kell: $data = $request->getParsedBody();

    $routes = require 'src/routes.php';
    $routes($app);

	include_once __DIR__ . '../src/Controllers/general_operations.php';
	include_once __DIR__ . '../src/Controllers/accountstatements_operations.php';
	include_once __DIR__ . '../src/Controllers/bankaccounts_operations.php';
	include_once __DIR__ . '../src/Controllers/beneficiaries_operations.php';
	include_once __DIR__ . '../src/Controllers/creditcards_operations.php';
	include_once __DIR__ . '../src/Controllers/currencies_operations.php';
	include_once __DIR__ . '../src/Controllers/dailyaccountbalances_operations.php';
	include_once __DIR__ . '../src/Controllers/foreigncurrencies_operations.php';
	include_once __DIR__ . '../src/Controllers/recurringtransfers_operations.php';
	include_once __DIR__ . '../src/Controllers/savings_operations.php';
	include_once __DIR__ . '../src/Controllers/savingtypes_operations.php';
	include_once __DIR__ . '../src/Controllers/transactions_operations.php';
	include_once __DIR__ . '../src/Controllers/users_operations.php';
	
    include_once __DIR__ . '../src/Controllers/xbank_operations.php';

	//include_once('currencies_operations.php');

	//require __DIR__ . '/src/user_operations.php';

	$app->run();
	?>