<?php
session_start();

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Illuminate\Database\Capsule\Manager as Capsule;

use \App\Controllers\TallyController\TallyController;

require __DIR__ . '/../vendor/autoload.php';

$app = new \Slim\App([
	"settings" => [
		"displayErrorDetails" => true,
		"db" => [
			"driver" => "mysql",
			"host" => "localhost",
			"database" => "xgasexpress",
			"username" => "root",
			"password" => "mysql",
			"charset" => "utf8",
			"collation" => "utf8_unicode_ci",
			"prefix" => ""
		],
	]
]);

$container = $app->getContainer();

$capsule = new Capsule;

$capsule->addConnection($container['settings']['db']);

$capsule->setAsGlobal();

$capsule->bootEloquent();

$container['view'] = function($container) {
	$views = new \Slim\Views\Twig(__DIR__ . '/../app/Views', ["cache" => false,]);

	$views->addExtension(new \Slim\Views\TwigExtension(
		$container->router,
		$container->request->getUri()
	));
	return $views;
};

$container['db'] = function($container) {
	return $caspule;
};

$container['GasExpressController'] = function($container) {
   return new App\Controllers\GasExpressController\GasExpressController($container);
};

$container['GCMController'] = function($container) {
   return new App\Controllers\GCMController\GCMController($container);
};

require __DIR__ . '/../app/routers.php';

?>
