<?php
namespace App\Controllers;
use Core\AppPage;
use Core\AppHTTPResponse;
use Core\Routing\AppRouter;
use Core\Config\AppConfig;

// Composer autoloader
if( !class_exists('Composer\\Autoload\\ClassLoader') )
{
	require_once __DIR__ . '/../../Libs/vendor/autoload.php';
}

// Google recaptcha component
use ReCaptcha\ReCaptcha;

abstract class BaseController 
{
	protected $page;
	protected $httpResponse;
	protected $router;
	protected $config;
	protected $currentModel;

	public function __construct(AppPage $page, AppHTTPResponse $httpResponse, AppRouter $router, AppConfig $config)   
	{
		$this->page = $page;
		$this->httpResponse = $httpResponse;
		$this->router = $router;
		$this->config = $config;

		session_start();
	}

	public function checkAction($action) 
	{
	    try {
			if(is_callable([$this, $action])) {
				return true;
			}
			else {
				throw new \RuntimeException("Technical error: sorry, we cannot find a content for your request. [Debug trace: Action called doesn't exist!]");
			}
		}
		catch(\RuntimeException $e) {
			return $e->getMessage();
		}
	}

	public function getCurrentModel($className)
	{
		$className = str_replace('Controller', 'Model', $className);
		$currentModel = new $className($this->httpResponse, $this->router, $this->config);
		return $currentModel;
	}

	public function checkGoogleRecaptchaResponse($grcResponse) // -> $_POST['g-recaptcha-response']
	{
		$reCaptcha = new ReCaptcha($this->config::$_params['googleRecaptcha']['secretKey']);
		if(isset($_POST['g-recaptcha-response'])) {
			$grcResponse = $reCaptcha->verify($grcResponse, $_SERVER['REMOTE_ADDR']);

			// Verify response
			if ($grcResponse->isSuccess()) {
				return true;
			}
			else {
				return [false, $grcResponse->getErrorCodes()];
			}
		}
		else {
			return false;
		}
	}

	// Dynamic $_POST check (token) index in addition to fight against CSRF
	public function generateTokenIndex($inputFormName)
	{
		if (!isset($_SESSION[$inputFormName])) {
			$_SESSION[$inputFormName] = $inputFormName . mt_rand(0,mt_getrandmax());
		}
		return $_SESSION[$inputFormName];
	}

	
	// Anti CSRF token
	public function generateTokenValue($varName) 
	{
		if (!isset($_SESSION[$varName])) {
		   	$_SESSION[$varName] = hash('sha256', $varName . bin2hex(openssl_random_pseudo_bytes(8)) . session_id());
		}
		return $_SESSION[$varName];
	}

	// Verify if token matches with POST value
	public function checkTokenValue($token, $varName)
	{
		return $token === $this->generateTokenValue($varName);
	}
}