<?php
namespace App\Controllers;
use Core\AppPage;
use Core\AppHTTPResponse;
use Core\Routing\AppRouter;
use Core\Config\AppConfig;

abstract class BaseController 
{
	protected $page;
	protected $httpResponse;
	protected $router;
	protected $currentModel;
	protected $config;

	public function __construct(AppPage $page, AppHTTPResponse $httpResponse, AppRouter $router)   
	{
		$this->page = $page;
		$this->httpResponse = $httpResponse;
		$this->router = $router;
		$this->config = AppConfig::getInstance();
	}

	public function checkAction($action) 
	{
	    //var_dump(is_callable([$this, $action]));
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

	public function getCurrentModel($className) {
		$className = str_replace('Controller', 'Model', $className);
		$currentModel = new $className($this->httpResponse, $this->router);
		return $currentModel;
	}
}