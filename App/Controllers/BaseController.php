<?php
namespace App\Controllers;
use Core\AppPage;
use Core\AppHTTPResponse;
use Core\Routing\AppRouter;
use Core\Config\AppConfig;

/**
 * Create a parent controller to use in each controller
 */
abstract class BaseController 
{
	/**
	 * @var AppPage instance
	 */
	protected $page;
	/**
	 * @var AppHTTPResponse instance
	 */
	protected $httpResponse;
	/**
	 * @var AppRouter instance
	 */
	protected $router;
	/**
	 * @var AppConfig instance
	 */
	protected $config;
	/**
	 * @var object: an instance of current model called by a particular controller
	 */
	protected $currentModel;

	/**
	 * Constructor
	 * @param AppPage $page 
	 * @param AppHTTPResponse $httpResponse 
	 * @param AppRouter $router 
	 * @param AppConfig $config 
	 * @return void
	 */
	public function __construct(AppPage $page, AppHTTPResponse $httpResponse, AppRouter $router, AppConfig $config)   
	{
		$this->page = $page;
		$this->httpResponse = $httpResponse;
		$this->router = $router;
		$this->config = $config;

		session_start();
	}

	/**
	 * Check if called method exists
	 * @param callable $action 
	 * @throws exception
	 * @return return boolean|string
	 */
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

	/**
	 * Get the model for a particular controller
	 * @param string $className: name of current class 
	 * @return object: an instance of current model
	 */
	public function getCurrentModel($className)
	{
		$className = str_replace('Controller', 'Model', $className);
		$currentModel = new $className($this->httpResponse, $this->router, $this->config);
		return $currentModel;
	}
}