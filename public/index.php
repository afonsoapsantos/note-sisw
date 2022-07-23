<?php 

session_start();

require_once(__DIR__ . '/../vendor/autoload.php');

use Slim\Factory\AppFactory;
//$app->addErrorMiddleware(false, true, true);
$app = AppFactory::create();

//$app->config('debug', true);

require_once("functions.php");
require_once("site.php");
require_once("admin.php");
require_once("manager.php");
require_once("customer.php");
require_once("technical.php");
require_once("farmworker.php");

$app->run();

 ?>
