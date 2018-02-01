<?php
use Core\Service\AppContainer;
use Core\Routing\AppRouter;

// Composer autoloader
if (!class_exists('Composer\\Autoload\\ClassLoader'))
{
	require_once __DIR__ . '/../Libs/vendor/autoload.php';
}
// DIC is instantiated.
$container = AppContainer::getInstance();

// Controller is called with a router.
$router = new AppRouter($_GET['url']);