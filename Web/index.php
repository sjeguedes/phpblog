<?php
use Core\Routing\AppRouter;

// Composer autoloader
if (!class_exists('Composer\\Autoload\\ClassLoader')) {
    require_once __DIR__ . '/../Libs/vendor/autoload.php';
}
// Controller is called with a unique router instance.
$router = AppRouter::getInstance($_GET['url']);
$router->init();
