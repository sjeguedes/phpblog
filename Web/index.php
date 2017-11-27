<?php
use Core\Psr4AutoloaderClass;
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

// Controller call with a router
$router = new AppRouter($_GET['url']);

//Debug
// $router->addRequest('/', function(){ echo 'test affiche home page'; }, 'blog|home', 'GET');
// $router->addRequest('/posts', function(){ echo 'test affiche tous les posts'; }, 'blog|post|islist', 'GET');
// $router->addRequest('/post/:id', 'blog|post|issingle', 'blog|post|issingle', 'GET');
// $router->addRequest('/post/:id', function($id){ echo 'test affiche post ' . $id; }, 'blog|post|issingle', 'GET');
// $route = $router->addRequest('/article/:slug-:id', function($slug, $id) use($router){ echo $router->useURL('post|single|slug', ['slug' => 'article-intro', 'id' => '5']); echo ' --------- test affiche post ' . $slug .'-'. $id;}, 'blog|post|issingle', 'GET')->useParameter('slug', '[a-z0-9\-]+')->useParameter('id', '[\d]+');

// $router->addRequest('/post/:id', function($id){ echo 'Formulaire de creation de post envoyé ' . $id; }, 'blog|post|issingle', 'POST');

// $router->checkRoute();




// $admin = new App\Models\Admin\AdminModel;
// //Admin 1
// echo $admin->generateUserActivationCode(1, 'michdur') . '<br>';
// echo $admin->generateUserPasswordEncryption('PB#admin#@md17');
// echo '<br><br>';
// //Member 1
// echo $admin->generateUserActivationCode(2, 'stephmoul') . '<br>';
// echo $admin->generateUserPasswordEncryption('PB#member#@sm17');

?>