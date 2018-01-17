<?php
use Core\Psr4AutoloaderClass;
use Core\Service\AppContainer;
use Core\Routing\AppRouter;

error_reporting(E_ALL);
ini_set("display_errors", 1);

// Call the autoloader
require_once __DIR__ . '/../Core/Psr4AutoLoaderClass.php';

// Instantiate the loader
$autoloader = new Psr4AutoLoaderClass();
// Register the autoloader
$autoloader->register();
// Register the base directories for the namespace
$autoloader->addNamespace('App', __DIR__ . '/../App');
$autoloader->addNamespace('Core', __DIR__ . '/../Core');

$container = AppContainer::getInstance();

// Controller call with a router
$router = new AppRouter($_GET['url']);